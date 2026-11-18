# Demuestra: ms-vets (BD propia db_vets) expone veterinarios con sus especialidades y paginación.

GET {{base_url}}/api/vets
HTTP 200
[Asserts]
header "Content-Type" contains "json"
jsonpath "$.data" count == 5
jsonpath "$.data[0].first_name" == "James"
jsonpath "$.data[1].specialties[0].name" == "radiology"
jsonpath "$.data[2].specialties" count == 2

# Página 2: el sexto veterinario
GET {{base_url}}/api/vets?page=2
HTTP 200
[Asserts]
jsonpath "$.data" count == 1
jsonpath "$.data[0].last_name" == "Jenkins"
