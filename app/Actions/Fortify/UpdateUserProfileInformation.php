<?php

namespace App\Actions\Fortify;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        $oldEmail = $user->email;

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }

        $this->syncWithPersona($user, $input, $oldEmail);
    }

    /**
     * Sincroniza los cambios de nombre, correo y foto con la entidad Persona / Autor
     * para que los cambios se reflejen de inmediato en los registros del Administrador.
     */
    protected function syncWithPersona(User $user, array $input, ?string $oldEmail): void
    {
        $persona = $user->persona;
        if (! $persona && $oldEmail) {
            $persona = Persona::where('p_correo', $oldEmail)->first();
        }
        if (! $persona) {
            $persona = Persona::where('p_correo', $input['email'])->first();
        }

        if ($persona) {
            if (! $persona->user_id) {
                $persona->user_id = $user->id;
            }

            $parts = preg_split('/\s+/', trim($input['name']));
            if (count($parts) === 1) {
                $persona->p_nombre = $parts[0];
            } elseif (count($parts) === 2) {
                $persona->p_nombre = $parts[0];
                $persona->p_apellido_paterno = $parts[1];
            } elseif (count($parts) >= 3) {
                $persona->p_nombre = implode(' ', array_slice($parts, 0, -2));
                $persona->p_apellido_paterno = $parts[count($parts) - 2];
                $persona->p_apellido_materno = $parts[count($parts) - 1];
            }

            $persona->p_correo = $input['email'];
            $persona->save();

            if (isset($input['photo']) && method_exists($persona, 'addMedia')) {
                try {
                    $persona->clearMediaCollection('foto_perfil');
                    $persona->addMedia($input['photo'])
                        ->preservingOriginal()
                        ->toMediaCollection('foto_perfil');
                } catch (\Throwable $e) {
                    // Silently ignore if media copy fails
                }
            }
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
