<script>
(function($) {
    'use strict';

    window.ignMultiselect = {

        init: function(wrapper, options, selected) {
            if (!wrapper || wrapper._ignMs) return;

            var fieldId     = wrapper.dataset.id;
            var placeholder = wrapper.dataset.placeholder || '';

            var $wrapper  = $(wrapper);
            var $select   = $wrapper.find('select');
            var $control  = $wrapper.find('.ign-ms-control');
            var $dropdown = $wrapper.find('.ign-ms-dropdown');
            var $optsCont = $wrapper.find('.ign-ms-options');
            var $chips    = $wrapper.find('.ign-ms-chips');
            var $search   = $wrapper.find('.ign-ms-search');
            var $empty    = $wrapper.find('.ign-ms-empty');
            var disabled  = $control.hasClass('ign-ms-disabled');

            var state = {
                options:  options || [],
                selected: selected ? selected.map(String) : [],
                open: false,
            };

            function syncSelect() {
                $select.find('option').each(function() {
                    this.selected = state.selected.indexOf($(this).val()) !== -1;
                });
            }

            function renderChips() {
                $chips.empty();
                state.selected.forEach(function(val) {
                    var opt = state.options.find(function(o) { return String(o.value) === val; });
                    var lbl = opt ? opt.label : val;
                    $chips.append(
                        $('<span class="ign-ms-chip">').append(
                            $('<span>').text(lbl)
                        ).append(
                            $('<button type="button" class="ign-ms-chip-remove" aria-label="Quitar">').html('<i class="ti-close"></i>').data('value', val)
                        )
                    );
                });
                $search.attr('placeholder', state.selected.length === 0 ? placeholder : '');
                syncSelect();
            }

            function renderOptions(filter) {
                filter = (filter || '').toLowerCase().trim();
                var visible = 0;
                $optsCont.find('.ign-ms-option').each(function() {
                    var val   = String($(this).data('value'));
                    var label = $(this).find('.ign-ms-option-label').text().toLowerCase();
                    var show  = !filter || label.indexOf(filter) !== -1;
                    $(this).toggleClass('hidden', !show);
                    if (show) visible++;
                    $(this).toggleClass('selected', state.selected.indexOf(val) !== -1);
                });
                $empty.toggle(visible === 0);
            }

            function open() {
                if (disabled) return;
                state.open = true;
                $control.addClass('open');
                $dropdown.show();
                renderOptions('');
                $search.focus();
            }

            function close() {
                state.open = false;
                $control.removeClass('open');
                $dropdown.hide();
                $search.val('');
            }

            function toggleOption(val) {
                val = String(val);
                var idx = state.selected.indexOf(val);
                if (idx === -1) { state.selected.push(val); }
                else { state.selected.splice(idx, 1); }
                renderChips();
                renderOptions($search.val());
                $wrapper.trigger('change', [state.selected]);
            }

            $control.on('click', function(e) {
                if ($(e.target).closest('.ign-ms-chip').length) return;
                state.open ? close() : open();
            });

            $control.on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); }
                if (e.key === 'Escape') close();
            });

            $search.on('input', function() {
                if (!state.open) open();
                renderOptions($(this).val());
            });

            $search.on('keydown', function(e) {
                if (e.key === 'Escape') { close(); $control.focus(); }
                if (e.key === 'Backspace' && $(this).val() === '' && state.selected.length) {
                    toggleOption(state.selected[state.selected.length - 1]);
                }
            });

            $optsCont.on('click', '.ign-ms-option:not(.hidden)', function(e) {
                e.stopPropagation();
                toggleOption($(this).data('value'));
            });

            $chips.on('click', '.ign-ms-chip-remove', function(e) {
                e.stopPropagation();
                toggleOption($(this).data('value'));
            });

            $(document).on('click.ign-ms-' + fieldId, function(e) {
                if (!$(e.target).closest($wrapper).length) close();
            });

            wrapper._ignMs = {
                getSelected: function() { return state.selected.slice(); },
                setSelected: function(vals) {
                    state.selected = (vals || []).map(String);
                    renderChips();
                    renderOptions('');
                },
                setOptions: function(opts) {
                    state.options = opts || [];
                    $optsCont.find('.ign-ms-option').remove();
                    $select.empty();
                    state.options.forEach(function(opt) {
                        $optsCont.append(
                            $('<button type="button" class="ign-ms-option">').attr('data-value', opt.value)
                                .toggleClass('selected', state.selected.indexOf(String(opt.value)) !== -1)
                                .append('<span class="ign-ms-check"><i class="ti-check"></i></span>')
                                .append($('<span class="ign-ms-option-label">').text(opt.label))
                        );
                        $select.append($('<option>').val(opt.value).text(opt.label));
                    });
                    renderChips();
                },
                reset: function() {
                    state.selected = [];
                    renderChips();
                    renderOptions('');
                },
                destroy: function() {
                    $(document).off('click.ign-ms-' + fieldId);
                    close();
                },
            };

            renderChips();
            renderOptions('');
        },
    };

})(jQuery);
</script>
