NUEVA ABSTRACCIÓN PARA BENEFITS

Benefit
id | plan_id | title | description | benefit_category_id | times_triggered | is_cumulative | is_active

-> TABLA: benefit_category
	id | name | key | description type | benefit_form_sections (nullable) | benefit_category_type_id (nullable)
	1 | "Descuento en Compras Posteriores" | "DISCOUNT_ON_NEXT_PURCHASE" |"Obtiene un monto fijo o porcentaje de descuento fijo en las compras posteriores a la vinculación" | 1 | 2
	2 | "Diferencial Compras Referidos" | "REFERRED_PURCHASE_DIFFERENTIAL" | "Es el diferencial que se adquiere por todos los hijos directos" | null | 4
	3 | "Acumula puntos para subir de nivel" | "ACCUMULATE_POINTS_LEVEL_UP" |"Acumula puntos por las compras realizadas por los referidos" | null | 4
	4 | "Beneficio por Primera Compra de Referido" | "FIRST_REFERRED_PURCHASE_BENEFIT" | "Beneficio fijo sobre el valor pagado por la primera compra de cada nuevo empresario que vincule directamente" | 2 | 4
	5 | "Adquirir nuevo permiso" | "NEW_PLATFORM_PERMISSION" |"Adquiere un nuevo permiso o acceso dentro de la plataforma" | 3 | 3
	6 | "Obtener una recompensa material, premiación o anuncio" | "MATERIAL_REWARD_OR_RECOGNITION" | "Adquiere una recompensa material, premiación o anuncio otorgado por la organización." | 4 | 5
	7 | "Bono monetario" | "MONETARY_BONUS" |"Adquiere un bono monetario como recompensa" | null | 1
	8 | "Bono de ingresos residuales" | "RESIDUAL_INCOME_BONUS" | "Adquiere una bonificación por ingresos residuales de sus referidos" | 5 | 4

-> TABLA: benefit_category_type
       id | label | key
	1 | Económico | ECONOMIC
	2 | Descuento | DISCOUNT
	3 | Permiso   | PERMISSION
	4 | Referidos | REFERRALS
	5 | Premio    | PRIZE

-> TABLA: benefit_form_sections
	id | benefit_category_id | section_label | section_key | section_order | is_active
	1 | 1 | "Registro de información" | "REGISTER_DATA" | 1 | 1
	2 | 2 | "Registro de información" | "REGISTER_DATA" | 1 | 1
	3 | 3 | "Registro de información" | "REGISTER_DATA" | 1 | 1
	4 | 4 | "Registro de información" | "REGISTER_DATA" | 1 | 1
	5 | 5 | "Registro de información" | "REGISTER_DATA" | 1 | 1

-> TABLA: benefit_form_fields
	id | benefit_form_section_id | field_label | field_key | field_type | is_required | help_text | validation_rules | is_active
	1 | 1 | "Cantidad de descuento" | "DISCOUNT_QUANTITY" | "number" | 1 | "Cantidad de descuento que obtendrá por las compras posteriores a adquirir este beneficio." | {"enabled": true, "maxLength": 100} | 1
	2 | 1 | "Seleccione Fijo o Porcentaje" | "DISCOUNT_TYPE" | "select" | 1 | "Tipo de descuento, si es fijo será un descuento fijo por producto, si es porcentaje, será un porcentaje determinado del valor del producto." | {"required": true, "options": ["Fijo", "Porcentaje"]} | 1
	
	3 | 2 | "Valor de beneficio" | "BENEFIT_QUANTITY" | "number" | 1 | "Valor de beneficio pago sobre la primer compra del referido." | {"enabled": true, "maxLength": 100} | 1
	4 | 2 | "Seleccione Fijo o Porcentaje" | "BENEFIT_TYPE" | "select" | 1 | "Tipo de beneficio, si es fijo será un beneficio fijo por la primer compra, si es porcentaje será sobre el valor de la primer compra." | {"required": true, "options": ["Fijo", "Porcentaje"]} | 1

	5 | 3 | "Seleccione el permiso" | "SELECT_PERMISSION" | "select" | 1 | "Seleccione el permiso que se adquiere por este beneficio." | {"required": true, "options": "METHOD[fetchPermissions]"} | 1
	
	6 | 4 | "Nombre del premio" | "PRIZE_NAME" | "text" | 1 | "Nombre del premio material, premiación o anuncio a obtener por este beneficio" | {"enabled": true, "maxLength": 100} | 1
	7 | 4 | "Descripción del premio" | "PRIZE_DESCRIPTION" | "text" | 1 | "Descripción precisa sobre qué se trata el beneficio en concreto." | {"enabled": true, "maxLength": 500} | 1

	8 | 5 | "Cantidad del bono" | "MONETARY_BONUS_QUANTITY" | "number" | 1 | Diligencie la cantidad del bono monetario que obtendrá como bonificación por este beneficio" | {"enabled": true, "maxLength": 100} | 1

-> benefit_form_answers
	id | benefit_id | benefit_form_field_id | answer
	
