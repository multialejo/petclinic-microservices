# Con ms-customers detenido:

# ms-vets no se ve afectado
GET {{base_url}}/api/vets
HTTP 200

# El gateway devuelve 503 JSON controlado en vez de colgarse
GET {{base_url}}/api/customers/owners/6
HTTP 503
[Asserts]
jsonpath "$.message" == "Service unavailable"

# ms-visits depende de ms-customers: degrada a 503, no a 500
POST {{base_url}}/api/visits
{ "owner_id": 6, "pet_id": 7, "date": "{{future_date}}", "description": "x" }
HTTP 503
