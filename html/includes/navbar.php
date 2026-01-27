<nav class="navbar navbar-expand-lg navbar-dark">
<div class="container-fluid">
<a class="navbar-brand" href="/dashboard.php">🛠️ MASTERTECH</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link" href="/dashboard.php">📊 Dashboard</a></li>
<li class="nav-item"><a class="nav-link" href="/incidencias.php">📋 Incidencias</a></li>
<li class="nav-item"><a class="nav-link" href="/crear_incidencia.php">➕ Nueva</a></li>
<li class="nav-item"><a class="nav-link" href="/clientes.php">👥 Clientes</a></li>
<li class="nav-item"><a class="nav-link" href="/tienda.php">🛒 Tienda</a></li>
<?php if(isset($_SESSION['rol']) && $_SESSION['rol']=='admin'): ?>
<li class="nav-item"><a class="nav-link" href="/usuarios.php">⚙️ Usuarios</a></li>
<?php endif; ?>
</ul>
<ul class="navbar-nav">
<li class="nav-item">
<span class="nav-link">
👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?> 
<span class="badge badge-<?php echo $_SESSION['rol']; ?>"><?php echo ucfirst($_SESSION['rol']); ?></span>
</span>
</li>
<li class="nav-item"><a class="nav-link" href="/logout.php">🚪 Salir</a></li>
</ul>
</div>
</div>
</nav>
