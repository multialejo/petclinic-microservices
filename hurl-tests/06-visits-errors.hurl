# Demuestra: ms-visits valida contra ms-customers y rechaza datos inconsistentes.

# Mascota inexistente (ms-customers responde 404)
POST {{base_url}}/api/visits
{ "owner_id": 6, "pet_id": 99999, "date": "{{future_date}}", "description": "x" }
HTTP 404

# La mascota 1 es de George Franklin (owner 1), no del owner 6
POST {{base_url}}/api/visits
{ "owner_id": 6, "pet_id": 1, "date": "{{future_date}}", "description": "x" }
HTTP 422

# Fecha no futura
POST {{base_url}}/api/visits
{ "owner_id": 6, "pet_id": 7, "date": "{{past_date}}", "description": "x" }
HTTP 422
[Asserts]
jsonpath "$.errors.date" exists

# Falta pet_ids
GET {{base_url}}/api/visits
HTTP 422
[Asserts]
jsonpath "$.errors.pet_ids" exists
