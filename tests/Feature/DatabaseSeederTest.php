<?php

use Database\Seeders\DatabaseSeeder;

test('la suite completa de DatabaseSeeder se ejecuta al 100% sin errores de columnas o relaciones', function () {
    $this->seed(DatabaseSeeder::class);

    // Verificar que los datos clave existan en la base de datos
    $this->assertDatabaseHas('roles', ['name' => 'administrador']);
    $this->assertDatabaseHas('area_tematicas', ['at_nombre' => 'Agropecuario']);
    $this->assertDatabaseHas('tipo_articulos', ['tar_nombre' => 'Artículo Original']);
    $this->assertDatabaseHas('cms_configuracion', ['cfg_siglas' => 'IETA']);
    $this->assertDatabaseCount('cms_autores', 53);
    $this->assertDatabaseHas('personas', ['p_nombre' => 'Ramiro', 'p_apellido_paterno' => 'Coa']);
});
