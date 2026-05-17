<?php

namespace Modules\Plans\Services;

use Modules\Plans\Repositories\PlanRepository;
use Illuminate\Support\Facades\File;
use App\Exceptions\InvalidSvgException;
use Exception;

class PlanService
{
    const IMAGE_DIR = 'uploads/images/plans';

    protected $planRepository;

    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    public function storePlan(array $data)
    {
        $count = $this->planRepository->count();
        $final = min(max(1, (int) $data['order']), $count + 1);

        $this->planRepository->incrementOrderFrom($final);

        $data['order']         = $final;
        $data['is_active']     = isset($data['is_active']) ? true : false;
        $data['is_life_title'] = $this->toBoolean($data['is_life_title'] ?? false);
        $data['image']         = isset($data['image']) && $data['image'] ? $this->saveImage($data['image']) : null;
        $data['styles']        = $this->buildStyles($data);

        return $this->planRepository->create($data);
    }

    public function updatePlan($id, array $data)
    {
        $plan  = $this->planRepository->findById($id);
        $count = $this->planRepository->count();
        $old   = $plan->order;
        $final = min(max(1, (int) $data['order']), $count);

        if ($final !== $old) {
            if ($final > $old) {
                $this->planRepository->shiftBetween($old + 1, $final, -1);
            } else {
                $this->planRepository->shiftBetween($final, $old - 1, +1);
            }
        }

        $data['order']         = $final;
        $data['is_active']     = isset($data['is_active']) ? true : false;
        $data['is_life_title'] = $this->toBoolean($data['is_life_title'] ?? false);

        // Imagen
        if (!empty($data['image'])) {
            $this->deleteImage($plan->image);
            $data['image'] = $this->saveImage($data['image']);
        } elseif (!empty($data['remove_image'])) {
            $this->deleteImage($plan->image);
            $data['image'] = null;
        } else {
            unset($data['image']);
        }

        // Styles (color + icon)
        $existing        = is_array($plan->styles) ? $plan->styles : [];
        $data['styles']  = $this->buildStyles($data, $existing);

        return $this->planRepository->update($plan, $data);
    }

    public function deletePlan($id)
    {
        $plan = $this->planRepository->findById($id, ['planChildren']);
        if ($plan->planChildren->count() > 0) {
            throw new Exception('No se puede eliminar: el plan tiene ' . $plan->planChildren->count() . ' subplan(es) asociado(s).');
        }

        $this->deleteImage($plan->image);

        return $this->planRepository->delete($plan);
    }

    public function reorder(array $ids): void
    {
        $this->planRepository->reorder($ids);
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    /**
     * Build the styles JSON from request data.
     * Merges new values on top of existing styles so unrelated keys are preserved.
     */
    private function buildStyles(array $data, array $existing = []): ?array
    {
        $styles = $existing;

        // Primary color
        if (isset($data['primary_color']) && $data['primary_color']) {
            $styles['primaryColor'] = $data['primary_color'];
        } else {
            unset($styles['primaryColor']);
        }

        // Icon SVG
        if (!empty($data['icon'])) {
            $styles['icon'] = $this->sanitizeSvg($data['icon']);
        } elseif (isset($data['remove_icon']) && $data['remove_icon']) {
            unset($styles['icon']);
        }
        // If neither provided, keep existing icon unchanged

        return empty($styles) ? null : $styles;
    }

    /**
     * Strip all attributes and elements that are not safe in an SVG.
     * Only allows a whitelist of SVG-specific elements and presentation attributes.
     * Returns sanitized SVG string.
     *
     * @throws InvalidSvgException if the input is not a valid SVG document.
     */
    private function sanitizeSvg(string $raw): string
    {
        $raw = trim($raw);

        // Must start with <svg (case-insensitive)
        if (!preg_match('/^\s*<svg[\s>]/i', $raw)) {
            throw new InvalidSvgException('El icono debe ser un SVG válido (debe comenzar con la etiqueta svg).');
        }

        // Block PHP processing instructions and server-side injection vectors
        if (preg_match('/<\?(?:php|=)/i', $raw)) {
            throw new InvalidSvgException('El SVG contiene contenido no permitido.');
        }

        // Parse with DOMDocument to validate well-formed XML
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($raw, LIBXML_NONET | LIBXML_NOBLANKS);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        if (!$loaded || !empty($errors)) {
            throw new InvalidSvgException('El SVG no es un XML válido.');
        }

        $root = $dom->documentElement;
        if (strtolower($root->tagName) !== 'svg') {
            throw new InvalidSvgException('El documento debe tener un elemento raíz svg.');
        }

        // Allowed SVG elements (presentational, no scripts/foreignObject/etc.)
        $allowedElements = [
            'svg', 'g', 'path', 'circle', 'ellipse', 'rect', 'line',
            'polyline', 'polygon', 'text', 'tspan', 'defs', 'title',
            'desc', 'use', 'symbol', 'clippath', 'mask', 'lineargradient',
            'radialgradient', 'stop', 'pattern',
        ];

        // Allowed attributes (no event handlers, no xlink:href to external, etc.)
        $allowedAttributes = [
            // Core
            'id', 'class', 'style',
            // SVG structural
            'xmlns', 'xmlns:xlink', 'version', 'viewbox', 'width', 'height',
            'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry',
            'd', 'points', 'transform', 'clip-path', 'mask',
            // Presentation
            'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width',
            'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray',
            'stroke-dashoffset', 'stroke-opacity', 'opacity',
            'font-size', 'font-family', 'font-weight', 'text-anchor',
            'dominant-baseline', 'letter-spacing',
            // Gradient / pattern
            'offset', 'stop-color', 'stop-opacity',
            'gradientunits', 'gradienttransform', 'spreadmethod',
            'fx', 'fy', 'patternunits', 'patterntransform',
            // Clip/mask
            'clippathoptions', 'clippathunits',
            // Use / symbol
            'href', 'xlink:href',
            // Accessibility
            'role', 'aria-label', 'aria-hidden',
            // Preserve aspect ratio
            'preserveaspectratio',
        ];

        $this->sanitizeDomNode($dom->documentElement, $allowedElements, $allowedAttributes);

        return $dom->saveXML($dom->documentElement);
    }

    /**
     * Recursively sanitize DOM elements: remove disallowed elements and attributes.
     */
    private function sanitizeDomNode(\DOMElement $node, array $allowedElements, array $allowedAttributes): void
    {
        $toRemove = [];

        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                if (!in_array(strtolower($child->tagName), $allowedElements, true)) {
                    $toRemove[] = $child;
                } else {
                    $this->sanitizeElementAttributes($child, $allowedAttributes);
                    $this->sanitizeDomNode($child, $allowedElements, $allowedAttributes);
                }
            } elseif ($child->nodeType === XML_PI_NODE) {
                $toRemove[] = $child;
            }
        }

        foreach ($toRemove as $el) {
            $node->removeChild($el);
        }
    }

    private function sanitizeElementAttributes(\DOMElement $element, array $allowedAttributes): void
    {
        $attrRemove = [];

        foreach ($element->attributes as $attr) {
            if (!$attr instanceof \DOMAttr) {
                continue;
            }
            if ($this->isAttributeBlocked($attr, $allowedAttributes)) {
                $attrRemove[] = $attr->name;
            }
        }

        foreach ($attrRemove as $attrName) {
            $element->removeAttribute($attrName);
        }
    }

    private function isAttributeBlocked(\DOMAttr $attr, array $allowedAttributes): bool
    {
        $name    = strtolower($attr->name);
        $isHref  = ($name === 'href' || $name === 'xlink:href');

        return !\in_array($name, $allowedAttributes, true)
            || preg_match('/javascript\s*:/i', $attr->value)
            || $isHref && preg_match('/^\s*data\s*:/i', $attr->value);
    }

    private function saveImage($file): string
    {
        $dir = public_path(self::IMAGE_DIR);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0777, true);
        }

        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);

        return self::IMAGE_DIR . '/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }

    private function toBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array($value, [1, '1', 'true', 'on', 'yes'], true);
    }
}
