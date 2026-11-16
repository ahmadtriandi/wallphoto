<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
| Channel 'gallery' bersifat public — tidak perlu autentikasi user.
| Semua client yang terhubung akan menerima event foto baru.
*/

// Public channel — semua orang bisa subscribe
Broadcast::channel('gallery', function () {
    return true;
});
