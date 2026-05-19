<?php

function view(string $path, array $data = []): void
{
    extract($data);
    require APP_ROOT . "/" . ltrim($path, "/");
}
