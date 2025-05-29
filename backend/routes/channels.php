<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('project.deadline', function ($user) {
    return true; // Or apply any access logic you want
});
