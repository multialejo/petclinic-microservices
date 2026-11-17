# Demuestra: ms-customers (BD propia db_customers): consulta de dueños, mascotas y tipos.

GET {{base_url}}/api/customers/types
HTTP 200
[Asserts]
jsonpath "$.data[0].name" == "bird"
jsonpath "$.data[1].name" == "cat"

# Búsqueda por prefijo de apellido
GET {{base_url}}/api/customers/owners?last_name=Cole
HTTP 200
[Asserts]
jsonpath "$.data" count == 1
jsonpath "$.data[0].first_name" == "Jean"

# Ficha del dueño 6 con sus mascotas y tipo
GET {{base_url}}/api/customers/owners/6
HTTP 200
[Asserts]
jsonpath "$.data.last_name" == "Coleman"
jsonpath "$.data.pets" count == 2
jsonpath "$.data.pets[1].type.name" == "cat"

# Mascota con datos de su dueño (la usa ms-visits internamente)
GET {{base_url}}/api/customers/pets/7
HTTP 200
[Asserts]
jsonpath "$.data.name" == "Samantha"
jsonpath "$.data.owner_id" == 6

# Recurso inexistente
GET {{base_url}}/api/customers/owners/99999
HTTP 404
[Asserts]
header "Content-Type" contains "json"
