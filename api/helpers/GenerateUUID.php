<?php

function generateUUID() {
    return bin2hex(random_bytes(16)); // Generate a random UUID (128-bit)
}

?>