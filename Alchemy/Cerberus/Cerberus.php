<?php
namespace Alchemy\Cerberus;

class Cerberus
{
    private static $instance;

    protected $status;
    protected $userLogged;

    public static function getInstance()
    {
        if (! self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function authentication($username, $password)
    {
        $user = UserQuery::create()
            ->filterByUsrUsername($username)
            ->findOne();

        if (! is_null($user)) {
            if (md5($password) == $user->getUsrPassword()) {
                $this->userLogged = new StdClass();
                $this->userLogged->user  = $user;
                $this->userLogged->roles = array();
                $this->userLogged->name = $user->getUsrLastName() . " " . $user->getUsrFistName();
                $this->userLogged->roles    = array();
                $this->userLogged->permisos = array();

                // getting user roles
                $userRoles = UserRolQuery::create()
                    ->filterByUser($user)
                    ->orderByRolName()
                    ->find();

                foreach ($usuarioRoles as $usuarioRol) {
                    $this->userLogged->roles[] = $usuarioRol->getRol()->getRolCodigo();
                    $this->userLogged->usuarioRoles[] = $usuarioRol->getRol()->getRolDescripcion();

                    $rolPermisos = RolPermisoQuery::create()
                        ->filterByRol($usuarioRol->getRol())
                        ->orderByPermId()
                        ->find();

                    foreach ($rolPermisos as $rolPermiso) {
                        $permission = $rolPermiso->getPermiso();
                        $this->userLogged->permisos[] = $permission->getPermCodigo();
                    }
                }

                $this->userLogged->usuarioRoles = implode(', ', $this->userLogged->usuarioRoles);
                $this->result->success = true;
                $this->result->message = 'Autenticado Satisfactoriamente';

                $this->persist();
            } else {
                $this->result->success = false;
                $this->result->message = 'Contraseña Inválida!';
            }
        } else {
            $this->result->success = false;
            $this->result->message = 'Usuario inválido!';
        }

        return $this->result;
    }
}
