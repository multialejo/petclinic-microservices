# Con ms-vets detenido: ms-customers y ms-visits siguen operando.
GET {{base_url}}/api/customers/owners
HTTP 200

GET {{base_url}}/api/customers/pets/1
HTTP 200

GET {{base_url}}/api/customers/types
HTTP 200

# Agendar una visita no depende de ms-vets
POST {{base_url}}/api/visits
{ "owner_id": 1, "pet_id": 1, "date": "{{future_date}}", "description": "Independencia de despliegue" }
HTTP 201

# Solo lo propio de ms-vets degrada, con 503 JSON controlado
GET {{base_url}}/api/vets
HTTP 503
[Asserts]
jsonpath "$.message" == "Service unavailable"
