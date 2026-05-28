<?php

use Illuminate\Support\Facades\Broadcast;

/*
 * Canal privado por usuário — somente o próprio usuário pode se inscrever.
 * Usado para notificações em tempo real (motorista recebe pedidos,
 * passageiro recebe confirmações, etc.)
 */
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
