<?php

namespace App\Console\Commands\Notaria;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ActualizarUsuariosNotaria extends Command
{
    protected $signature = 'notaria:actualizar-usuarios {--dry-run : Muestra los cambios sin aplicarlos}';

    protected $description = 'Upsert de usuarios y roles de la Notaría Alex Herrera (empresa_id=15): crea/actualiza username, contraseña, rol y puede_facturar.';

    const EMPRESA_ID = 15;

    /**
     * match_username: username actual en BD a buscar (solo cuando difiere
     * del username final deseado). Si es null, se busca por 'username'.
     *
     * name: solo se usa al CREAR un usuario nuevo; a los existentes no se
     * les toca el nombre (los nombres de esta tabla son abreviados/informales
     * frente al nombre legal completo ya guardado).
     *
     * password: null significa "no tocar la contraseña" (caso Karla).
     */
    protected function usuarios(): array
    {
        return [
            [
                'match_username' => null,
                'username' => 'alex',
                'name' => 'Alex Saul Herrera Arias',
                'password' => 'alex2026%',
                'rol' => 'admin',
                'puede_facturar' => true,
            ],
            [
                'match_username' => null,
                'username' => 'Karla',
                'name' => 'Karla',
                'password' => null,
                'rol' => 'admin',
                'puede_facturar' => true,
            ],
            [
                'match_username' => 'WalterQuispe',
                'username' => 'Walter',
                'name' => 'Walter Arias Quispe',
                'password' => 'walter2026%',
                'rol' => 'escrituras',
                'puede_facturar' => false,
            ],
            [
                'match_username' => null,
                'username' => 'James',
                'name' => 'James Encarnacion',
                'password' => 'encarnacion2026%',
                'rol' => 'escrituras',
                'puede_facturar' => false,
            ],
            [
                'match_username' => null,
                'username' => 'Cynthia',
                'name' => 'Cynthia Santiago',
                'password' => 'cynthia2026%',
                'rol' => 'mixto',
                'puede_facturar' => true,
            ],
            [
                'match_username' => null,
                'username' => 'Pablo',
                'name' => 'Pablo Espinoza',
                'password' => 'pablo2026%',
                'rol' => 'escrituras',
                'puede_facturar' => false,
            ],
            [
                'match_username' => null,
                'username' => 'Angela',
                'name' => 'Angela Diaz Pozo',
                'password' => 'angela2026%',
                'rol' => 'prescripciones',
                'puede_facturar' => false,
            ],
            [
                'match_username' => null,
                'username' => 'Christian',
                'name' => 'Christian Aguirre',
                'password' => 'christian2026%',
                'rol' => 'notificaciones',
                'puede_facturar' => false,
            ],
            [
                'match_username' => 'Luciano',
                'username' => 'Romel',
                'name' => 'Solis Luciano Romel Rosio',
                'password' => 'luciano2026%',
                'rol' => 'escrituras',
                'puede_facturar' => false,
            ],
            [
                'match_username' => 'Silva Cardenas',
                'username' => 'Silva',
                'name' => 'Silva Cardenas',
                'password' => 'silva2026%',
                'rol' => 'legalizaciones',
                'puede_facturar' => false,
            ],
            [
                'match_username' => null,
                'username' => 'Diana',
                'name' => 'Heidy Diana Lavado',
                'password' => 'heidy2026%',
                'rol' => 'legalizaciones',
                'puede_facturar' => false,
            ],
            [
                'match_username' => null,
                'username' => 'Karina',
                'name' => 'Cila Karina Calero',
                'password' => 'cila2026%',
                'rol' => 'prescripciones',
                'puede_facturar' => false,
            ],
            [
                'match_username' => null,
                'username' => 'Natalia',
                'name' => 'Natalia Isabel Bazan Dueñas',
                'password' => 'natalia2026%',
                'rol' => 'legalizaciones',
                'puede_facturar' => false,
            ],
        ];
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $filas = [];

        DB::beginTransaction();

        try {
            foreach ($this->usuarios() as $u) {
                $buscarPor = $u['match_username'] ?? $u['username'];

                $existente = User::where('empresa_id', self::EMPRESA_ID)
                    ->where('username', $buscarPor)
                    ->first();

                $accion = $existente ? 'ACTUALIZAR' : 'CREAR';
                $rolAnterior = $existente->rol ?? '-';
                $facturaAnterior = $existente ? ($existente->puede_facturar ? 'Sí' : 'No') : '-';

                $atributos = [
                    'empresa_id' => self::EMPRESA_ID,
                    'username' => $u['username'],
                    'rol' => $u['rol'],
                    'activo' => true,
                    'puede_facturar' => $u['puede_facturar'],
                ];

                if (! $existente) {
                    $atributos['name'] = $u['name'];
                }

                $cambiaPassword = $u['password'] !== null;
                if ($cambiaPassword) {
                    $atributos['password'] = Hash::make($u['password']);
                }

                $filas[] = [
                    'accion' => $accion,
                    'username_anterior' => $existente->username ?? '-',
                    'username' => $u['username'],
                    'rol_anterior' => $rolAnterior,
                    'rol' => $u['rol'],
                    'factura_anterior' => $facturaAnterior,
                    'factura' => $u['puede_facturar'] ? 'Sí' : 'No',
                    'password' => $cambiaPassword ? 'se actualiza' : 'sin cambios',
                ];

                if ($existente) {
                    $existente->update($atributos);
                } else {
                    User::create($atributos);
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->table(
            ['Acción', 'Username antes', 'Username', 'Rol antes', 'Rol', 'Factura antes', 'Factura', 'Password'],
            array_map(fn ($f) => [
                $f['accion'], $f['username_anterior'], $f['username'],
                $f['rol_anterior'], $f['rol'], $f['factura_anterior'], $f['factura'], $f['password'],
            ], $filas)
        );

        $this->newLine();
        $this->warn('Sin resolver (no se tocan, requieren decisión aparte):');
        $this->line('- Silva: "autorizaciones de viaje" no está mapeado a ningún rol hoy.');
        $this->line('- Diana: "libros de actas" no existe como concepto en el sistema.');
        $this->line('- Ningún usuario tiene forzado el cambio de contraseña en el primer login (el campo no existe).');

        if ($dryRun) {
            DB::rollBack();
            $this->newLine();
            $this->info('DRY-RUN: no se aplicó ningún cambio (rollback automático).');
        } else {
            DB::commit();
            $this->newLine();
            $this->info('Cambios aplicados y confirmados.');
        }

        return self::SUCCESS;
    }
}
