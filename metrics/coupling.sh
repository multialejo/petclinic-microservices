#!/usr/bin/env bash
# Métricas de acoplamiento: monolito (As-Is) vs microservicios (To-Be). Solo lectura, no requiere Docker.
cd "$(dirname "$0")/.."
MONO=../spring-petclinic
J=$MONO/src/main/java/org/springframework/samples/petclinic
SCHEMA=$MONO/src/main/resources/db/mysql/schema.sql

echo "== MONOLITO (spring-petclinic) =="
echo "Artefactos desplegables : 1 (un solo jar)"
echo "Bases de datos          : 1 (tablas: $(grep -c 'CREATE TABLE' $SCHEMA))"
echo "Archivos Java / LOC     : $(find $J -name '*.java' | wc -l) / $(cat $(find $J -name '*.java') | wc -l)"
echo "Foreign keys totales    : $(grep -c 'FOREIGN KEY' $SCHEMA)"
echo "FK entre contextos      : $(grep -c 'REFERENCES pets' $SCHEMA) (visits -> pets)"
# imports entre paquetes de dominio distintos (owner, vet), excluyendo model/system compartidos
cross=0
for pkg in owner vet; do
  for other in owner vet; do
    [ "$pkg" = "$other" ] && continue
    n=$(grep -rE "^import org.springframework.samples.petclinic.$other\." $J/$pkg 2>/dev/null | wc -l)
    echo "Imports $pkg -> $other     : $n"; cross=$((cross+n))
  done
done
echo "Imports de clases compartidas (model.*): $(grep -rhE '^import org.springframework.samples.petclinic.model\.' $J | wc -l)"
echo "Imports cruzados entre dominios: $cross"

echo
echo "== MICROSERVICIOS =="
echo "Artefactos desplegables : 5 (ms-vets, ms-customers, ms-visits, frontend, gateway) + worker"
echo "Bases de datos          : 3 (instancias MySQL separadas)"
total_cross=0
for s in ms-vets ms-customers ms-visits frontend; do
  files=$(find $s/app -name '*.php' | wc -l); loc=$(cat $(find $s/app -name '*.php') | wc -l)
  # un servicio "importa" a otro si su código referencia el namespace/ruta de otro proyecto
  refs=0
  for o in ms-vets ms-customers ms-visits; do
    [ "$o" = "$s" ] && continue
    n=$(grep -rlE "\\\\?App\\\\.*$o|\.\./$o|$o/app" $s/app $s/routes $s/database 2>/dev/null | wc -l); refs=$((refs+n))
  done
  total_cross=$((total_cross+refs))
  echo "$s: $files archivos PHP / $loc LOC; referencias de código a otro servicio: $refs"
done
fk=$(grep -rE "foreign\(|constrained\(" ms-visits/database/migrations | wc -l)
echo "FK en ms-visits hacia otro servicio : $fk (visits.pet_id solo indexado)"
echo "Dependencias HTTP en runtime        :"
grep -rhoE "services\.(customers|vets|visits)\.url" ms-*/app | sort | uniq -c
echo "Imports cruzados entre servicios    : $total_cross"
