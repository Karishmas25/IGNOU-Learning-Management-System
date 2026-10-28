<?php

$password = "Admin@123";

$hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

if (password_verify($password, $hash)) {

    echo "Password is correct.";

} else {

    echo "Password is incorrect.";
}

?>