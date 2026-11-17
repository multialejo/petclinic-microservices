# Demuestra: el API Gateway (Nginx) es el único punto de entrada y enruta a los microservicios.

# Health del gateway
GET {{base_url}}/health
HTTP 200
[Asserts]
jsonpath "$.status" == "ok"

# Ruta inexistente -> 404 JSON (no una página HTML de Nginx)
GET {{base_url}}/api/no-existe
HTTP 404
[Asserts]
header "Content-Type" contains "json"
jsonpath "$.message" == "Not Found"

# Reescritura /api/customers/* -> ms-customers /api/*
GET {{base_url}}/api/customers/types
HTTP 200
[Asserts]
jsonpath "$.data" count == 6

# CORS preflight respondido por el gateway
OPTIONS {{base_url}}/api/vets
Origin: http://localhost:3000
Access-Control-Request-Method: GET
HTTP 204
