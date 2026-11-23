<?php
// apaga a sessão e volta para a tela de login
session_start();
session_destroy();
header('Location: ../login.html');
