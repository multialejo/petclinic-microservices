# Demuestra: alta y edición de dueños y mascotas, con validaciones (422).

# Crear dueño
POST {{base_url}}/api/customers/owners
{
  "first_name": "Demo",
  "last_name": "Expo",
  "address": "Av. Siempre Viva 742",
  "city": "Cochabamba",
  "telephone": "7012345678"
}
HTTP 201
[Captures]
owner_id: jsonpath "$.data.id"
[Asserts]
jsonpath "$.data.first_name" == "Demo"

# Leer lo creado
GET {{base_url}}/api/customers/owners/{{owner_id}}
HTTP 200
[Asserts]
jsonpath "$.data.city" == "Cochabamba"

# Actualizar
PUT {{base_url}}/api/customers/owners/{{owner_id}}
{
  "first_name": "Demo",
  "last_name": "Editado",
  "address": "Calle Nueva 1",
  "city": "La Paz",
  "telephone": "7012345678"
}
HTTP 200
[Asserts]
jsonpath "$.data.city" == "La Paz"

# Validación: teléfono de 9 dígitos
POST {{base_url}}/api/customers/owners
{
  "first_name": "Mal",
  "last_name": "Telefono",
  "address": "x",
  "city": "y",
  "telephone": "123456789"
}
HTTP 422
[Asserts]
jsonpath "$.errors.telephone" exists

# Crear mascota
POST {{base_url}}/api/customers/owners/{{owner_id}}/pets
{
  "name": "Firulais",
  "birth_date": "{{past_date}}",
  "type_id": 2
}
HTTP 201
[Captures]
pet_id: jsonpath "$.data.id"
[Asserts]
jsonpath "$.data.type.name" == "dog"

# Nombre duplicado (sin distinguir mayúsculas) para el mismo dueño
POST {{base_url}}/api/customers/owners/{{owner_id}}/pets
{
  "name": "FIRULAIS",
  "birth_date": "{{past_date}}",
  "type_id": 2
}
HTTP 422
[Asserts]
jsonpath "$.errors.name" exists

# Fecha de nacimiento futura
POST {{base_url}}/api/customers/owners/{{owner_id}}/pets
{
  "name": "Futuro",
  "birth_date": "{{future_date}}",
  "type_id": 2
}
HTTP 422
[Asserts]
jsonpath "$.errors.birth_date" exists

# Mascota de otro dueño no es editable por esta ruta
PUT {{base_url}}/api/customers/owners/{{owner_id}}/pets/7
{
  "name": "Intruso",
  "birth_date": "{{past_date}}",
  "type_id": 1
}
HTTP 404
