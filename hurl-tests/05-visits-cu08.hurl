# Demuestra: CU-08 Agendar visita, comunicación entre microservicios.
#   ms-visits --(HTTP síncrono + X-Internal-Token)--> ms-customers para validar mascota y dueño,
#   guarda la visita y dispara el evento VisitCreated (cola Redis, procesado por el worker).

# 1) Preparar datos en ms-customers
POST {{base_url}}/api/customers/owners
{
  "first_name": "Visita",
  "last_name": "Flujo",
  "address": "Calle 1",
  "city": "Cochabamba",
  "telephone": "7099999999"
}
HTTP 201
[Captures]
owner_id: jsonpath "$.data.id"

POST {{base_url}}/api/customers/owners/{{owner_id}}/pets
{
  "name": "Michi",
  "birth_date": "{{past_date}}",
  "type_id": 1
}
HTTP 201
[Captures]
pet_id: jsonpath "$.data.id"

# 2) Agendar la visita en ms-visits
POST {{base_url}}/api/visits
{
  "owner_id": {{owner_id}},
  "pet_id": {{pet_id}},
  "date": "{{future_date}}",
  "description": "Vacuna antirrábica (demo expo)"
}
HTTP 201
[Asserts]
jsonpath "$.data.pet_id" == {{pet_id}}
jsonpath "$.data.date" == "{{future_date}}"

# 3) La visita aparece al consultar por mascota
GET {{base_url}}/api/visits?pet_ids={{pet_id}}
HTTP 200
[Asserts]
jsonpath "$.data" count == 1
jsonpath "$.data[0].description" startsWith "Vacuna"

# 4) Visitas históricas del seed para varias mascotas a la vez
GET {{base_url}}/api/visits?pet_ids=7,8
HTTP 200
[Asserts]
jsonpath "$.data" count >= 4
