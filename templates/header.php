<?php
// templates/header.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аналитика цен</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-header">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-line me-2"></i>emg ↔ Ovoko Аналитика
            </a>
            <button class="navbar-toggler" type="button" data138-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="#dashboard">Дашборд</a></li>
                    <li class="nav-item"><a class="nav-link" href="#analytics">Таблица</a></li>
                    <li class="nav-item"><a class="nav-link" href="#charts">Графики</a></li>
                </ul>
                <span class="navbar-text">
                    <i class="fas fa-sync-alt me-1"></i> Последнее обновление: <?= date('d.m.Y H:i') ?>
                </span>
            </div>
        </div>
    </nav>
    <div class="container-fluid py-4">