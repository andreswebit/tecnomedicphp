<?php
/**
 * PROPUESTA VISUAL — Ficha Médica Rediseñada (versión compacta)
 * TecnoMedic · Paleta: #98c544 (lima) y #1aa5a5 (teal)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ficha Médica · Propuesta Compacta</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --lima:#98c544; --lima-dk:#7aad2e;
  --teal:#1aa5a5; --teal-dk:#148080; --teal-lt:#30c5c5;
  --navy:#0d1b2a; --navy-mid:#1a3354;
  --white:#ffffff; --bg:#edf1f0; --muted:#8aada9;
  --g100:#e2e8e6; --g300:#b0bfbc; --g600:#555f5e; --g800:#333a39;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--g800);min-height:100vh}

/* Header compacto */
.ficha-header{display:flex;align-items:center;gap:16px;background:var(--navy);color:#fff;
  padding:14px 20px}
.ficha-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--lima));
  display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700;flex-shrink:0}
.ficha-paciente{flex:1}
.ficha-paciente-nombre{font-size:0.95rem;font-weight:600;margin-bottom:3px}
.ficha-paciente-meta{display:flex;gap:10px;flex-wrap:wrap;font-size:0.72rem;opacity:0.85}
.ficha-badge{background:rgba(152,197,68,0.18);color:var(--lima);padding:3px 10px;border-radius:20px;
  font-size:0.68rem;font-weight:600;border:1px solid rgba(152,197,68,0.30)}

/* Tabs compactos */
.ficha-tabs{display:flex;background:#fff;border-bottom:1px solid var(--g100)}
.ficha-tab{padding:9px 14px;border:none;background:transparent;color:var(--g600);
  font-size:0.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px}
.ficha-tab.active{color:var(--teal);border-bottom:2px solid var(--teal)}
.ficha-tab .icon{font-size:0.85rem}

/* Cards compactas */
.ficha-card{background:#fff;border-radius:8px;margin-bottom:10px;box-shadow:0 1px 6px rgba(13,27,42,0.07)}
.ficha-card-header{display:flex;align-items:center;gap:8px;padding:10px 14px;border-bottom:1px solid var(--g100)}
.ficha-card-icon{width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;flex-shrink:0}
.ficha-card-icon.blue{background:rgba(26,165,165,0.12);color:var(--teal)}
.ficha-card-icon.green{background:rgba(152,197,68,0.12);color:var(--lima-dk)}
.ficha-card-icon.amber{background:rgba(245,158,11,0.12);color:#d97706}
.ficha-card-icon.red{background:rgba(224,85,85,0.12);color:#e05555}
.ficha-card-icon.purple{background:rgba(139,92,246,0.12);color:#7c3aed}
.ficha-card-title{font-size:0.88rem;font-weight:600;color:var(--navy);flex:1}

.ficha-card-body{padding:10px 12px}
.kv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px}
.kv-item{background:var(--bg);padding:8px;border-radius:6px}
.kv-label{font-size:0.65rem;font-weight:600;color:var(--muted);text-transform:uppercase;margin-bottom:2px}
.kv-value{font-size:0.82rem;color:var(--g800)}

.ficha-field{margin-bottom:10px}
.ficha-field-label{font-size:0.68rem;font-weight:600;color:var(--g600);margin-bottom:3px}
.ficha-textarea{width:100%;padding:8px 10px;border:1px solid var(--g100);border-radius:6px;
  font-size:0.78rem;background:var(--bg);color:var(--g800);resize:vertical;min-height:55px}
.ficha-textarea:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 2px rgba(26,165,165,0.15);background:#fff}

/* Tabla compacta */
.ficha-table{width:100%;border-collapse:collapse;font-size:0.76rem}
.ficha-table th{padding:7px 9px;text-align:left;color:var(--muted);border-bottom:1px solid var(--g100);white-space:nowrap}
.ficha-table td{padding:7px 9px;border-bottom:1px solid var(--g100);vertical-align:middle}
.ficha-table tr:last-child td{border-bottom:none}
.ficha-table tr:hover td{background:rgba(237,241,240,0.5)}
.badge-area{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:10px;font-size:0.68rem;font-weight:600}
.badge-area.audiologia{background:rgba(139,92,246,0.12);color:#7c3aed}
.badge-area.hiperbarica{background:rgba(26,165,165,0.12);color:var(--teal-dk)}
.badge-area.nutricion{background:rgba(152,197,68,0.12);color:var(--lima-dk)}
.badge-area.ortopedia{background:rgba(245,158,11,0.12);color:#d97706}
.badge-area.equipamiento{background:rgba(224,85,85,0.12);color:#e05555}

/* Badges de estado medicamento */
.badge-estado{display:inline-flex;align-items:center;padding:2px 8px;border-radius:10px;font-size:0.68rem;font-weight:600}
.badge-estado.activo{background:rgba(152,197,68,0.12);color:var(--lima-dk)}
.badge-estado.suspendido{background:rgba(245,158,11,0.12);color:#d97706}
.badge-estado.finalizado{background:rgba(138,138,138,0.12);color:#888}

/* Botones compactos */
.ficha-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;
  font-family:'Poppins',sans-serif;font-size:0.75rem;font-weight:600;cursor:pointer;border:none;transition:all .15s;white-space:nowrap}
.ficha-btn-primary{background:var(--lima);color:#fff;box-shadow:0 2px 6px rgba(152,197,68,0.25)}
.ficha-btn-primary:hover{background:var(--lima-dk);transform:translateY(-1px)}
.ficha-btn-outline{background:transparent;color:var(--teal);border:1px solid var(--teal)}
.ficha-btn-outline:hover{background:rgba(26,165,165,0.06)}
.ficha-btn-teal{background:var(--teal);color:#fff;box-shadow:0 2px 6px rgba(26,165,165,0.25)}
.ficha-btn-teal:hover{background:var(--teal-lt);transform:translateY(-1px)}
.ficha-btn-sm{padding:5px 9px;font-size:0.7rem}
.ficha-btn-xs{padding:3px 7px;font-size:0.68rem;border-radius:5px}
.ficha-btn-icon{width:26px;height:26px;padding:0;justify-content:center}

/* Footer compacto */
.ficha-card-footer{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;
  border-top:1px solid var(--g100);font-size:0.7rem}
.ficha-card-footer .date{color:var(--muted)}

/* Estudios / medicamentos lista */
.lista-item{display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--g100)}
.lista-item:last-child{border-bottom:none}
.lista-icon{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;flex-shrink:0}
.lista-icon.blue{background:rgba(26,165,165,0.10);color:var(--teal)}
.lista-icon.purple{background:rgba(139,92,246,0.10);color:#7c3aed}
.lista-icon.green{background:rgba(152,197,68,0.10);color:var(--lima-dk)}
.lista-info{flex:1;min-width:0}
.lista-nombre{font-size:0.80rem;font-weight:600;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lista-meta{font-size:0.70rem;color:var(--g600);margin-top:1px;display:flex;gap:8px;flex-wrap:wrap}
.lista-actions{display:flex;gap:4px;flex-shrink:0}

/* Inputs en tabla */
.tabla-input{width:100%;padding:4px 6px;border:1px solid var(--g100);border-radius:4px;
  font-size:0.75rem;background:#fff;color:var(--g800)}
.tabla-input:focus{outline:none;border-color:var(--teal)}

/* Responsive */
@media(max-width:600px){
  .ficha-header{flex-wrap:wrap}
  .ficha-tabs{overflow-x:auto;font-size:0.7rem;padding:6px}
  .ficha-tabs .ficha-tab{padding:7px 10px}
  .ficha-body{padding:10px}
  .kv-grid{grid-template-columns:1fr 1fr}
}</style>
</head>
<body>

<!-- HEADER -->
<div class="ficha-header">
  <div class="ficha-avatar">MG</div>
  <div class="ficha-paciente">
    <div class="ficha-paciente-nombre">Gómez, María Florencia</div>
    <div class="ficha-paciente-meta">
      <span>🪪 38.241.556</span>
      <span>📞 3794-123456</span>
      <span>🏥 IOSCOR</span>
    </div>
  </div>
  <div class="ficha-badge">Activo</div>
</div>

<!-- TABS -->
<div class="ficha-tabs">
  <button class="ficha-tab active"><span class="icon">📋</span>Historia</button>
  <button class="ficha-tab"><span class="icon">💉</span>Tratamientos</button>
  <button class="ficha-tab"><span class="icon">🧪</span>Estudios</button>
  <button class="ficha-tab"><span class="icon">💊</span>Medicamentos</button>
</div>

<div style="padding:10px">

  <!-- CARD 1: Datos personales -->
  <div class="ficha-card">
    <div class="ficha-card-header">
      <div class="ficha-card-icon blue">👤</div>
      <div class="ficha-card-title">Datos del paciente</div>
      <button class="ficha-btn ficha-btn-xs ficha-btn-outline">✏️</button>
    </div>
    <div class="ficha-card-body">
      <div class="kv-grid">
        <div class="kv-item"><div class="kv-label">Nombre</div><div class="kv-value">M. Florencia Gómez</div></div>
        <div class="kv-item"><div class="kv-label">DNI</div><div class="kv-value">38.241.556</div></div>
        <div class="kv-item"><div class="kv-label">Tel</div><div class="kv-value">3794-123456</div></div>
        <div class="kv-item"><div class="kv-label">Email</div><div class="kv-value">m.gomez@mail.com</div></div>
        <div class="kv-item"><div class="kv-label">Obra Social</div><div class="kv-value">IOSCOR</div></div>
        <div class="kv-item"><div class="kv-label">Nac.</div><div class="kv-value">15/03/1978</div></div>
      </div>
    </div>
  </div>

  <!-- CARD 2: Historia clínica -->
  <div class="ficha-card">
    <div class="ficha-card-header">
      <div class="ficha-card-icon green">📋</div>
      <div class="ficha-card-title">Historia clínica</div>
      <button class="ficha-btn ficha-btn-xs ficha-btn-outline">✏️ Modificar</button>
    </div>
    <div class="ficha-card-body">
      <div class="kv-grid" style="grid-template-columns:1fr 1fr">
        <div class="kv-item"><div class="kv-label">Antecedentes</div><div class="kv-value">HTA controlada · DM2</div></div>
        <div class="kv-item"><div class="kv-label">Diagnóstico</div><div class="kv-value">Síndrome metabólico</div></div>
      </div>
      <div class="ficha-field" style="margin-top:8px">
        <div class="ficha-field-label">Observaciones</div>
        <textarea class="ficha-textarea" rows="2">Control trimestral. Evaluar cámara hiperbárica para pie diabético.</textarea>
      </div>
    </div>
    <div class="ficha-card-footer">
      <span class="date">Última act.: 28/08/2026 · Dr. Pérez</span>
      <button class="ficha-btn ficha-btn-sm ficha-btn-primary">💾 Guardar</button>
    </div>
  </div>

  <!-- CARD 3: Tratamientos -->
  <div class="ficha-card">
    <div class="ficha-card-header">
      <div class="ficha-card-icon amber">💉</div>
      <div class="ficha-card-title">Tratamientos</div>
      <button class="ficha-btn ficha-btn-xs ficha-btn-outline">➕</button>
    </div>
    <div class="ficha-card-body" style="padding:0 0 6px">
      <table class="ficha-table">
        <thead><tr><th>Fecha</th><th>Área</th><th>Profesional</th><th>Descripción</th></tr></thead>
        <tbody>
          <tr>
            <td>28/08/26</td>
            <td><span class="badge-area hiperbarica">🫁 Hiperbárica</span></td>
            <td>Dr. Pérez</td>
            <td>Sesión 12. Buena tolerancia.</td>
          </tr>
          <tr>
            <td>15/07/26</td>
            <td><span class="badge-area nutricion">🥗 Nutrición</span></td>
            <td>Lic. García</td>
            <td>Plan alimentario personalizado.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- CARD 4: Medicamentos recetados -->
  <div class="ficha-card">
    <div class="ficha-card-header">
      <div class="ficha-card-icon purple">💊</div>
      <div class="ficha-card-title">Medicamentos recetados</div>
      <button class="ficha-btn ficha-btn-xs ficha-btn-teal">➕ Agregar</button>
    </div>
    <div class="ficha-card-body" style="padding:0 0 6px">
      <table class="ficha-table">
        <thead>
          <tr>
            <th>Medicamento</th>
            <th>Droga</th>
            <th>Dosis</th>
            <th>Frecuencia</th>
            <th>Vía</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Losartan</strong></td>
            <td>Losartán potásico</td>
            <td>50 mg</td>
            <td>1 vez/día</td>
            <td>Oral</td>
            <td><span class="badge-estado activo">● Activo</span></td>
          </tr>
          <tr>
            <td><strong>Metformina</strong></td>
            <td>Clorhidrato de metformina</td>
            <td>850 mg</td>
            <td>2 veces/día</td>
            <td>Oral</td>
            <td><span class="badge-estado activo">● Activo</span></td>
          </tr>
          <tr>
            <td><strong>Enalapril</strong></td>
            <td>Enalapril maleato</td>
            <td>20 mg</td>
            <td>1 vez/día</td>
            <td>Oral</td>
            <td><span class="badge-estado suspendido">● Suspendido</span></td>
          </tr>
          <tr>
            <td><strong>Insulina</strong></td>
            <td>Insulina glargina</td>
            <td>20 UI</td>
            <td>1 vez/día</td>
            <td>Subcutánea</td>
            <td><span class="badge-estado finalizado">● Finalizado</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- CARD 5: Estudios -->
  <div class="ficha-card">
    <div class="ficha-card-header">
      <div class="ficha-card-icon red">🧪</div>
      <div class="ficha-card-title">Estudios</div>
      <button class="ficha-btn ficha-btn-xs ficha-btn-primary">⬆️ Subir</button>
    </div>
    <div class="ficha-card-body">
      <div class="lista-item">
        <div class="lista-icon blue">📄</div>
        <div class="lista-info">
          <div class="lista-nombre">Laboratorio_clinico_ago2026.pdf</div>
          <div class="lista-meta">🧪 Laboratorio · 20/08/26 · Dr. Pérez</div>
        </div>
        <div class="lista-actions">
          <button class="ficha-btn ficha-btn-xs ficha-btn-outline" title="Ver">👁️</button>
          <button class="ficha-btn ficha-btn-xs ficha-btn-outline" title="Descargar">⬇️</button>
        </div>
      </div>
      <div class="lista-item">
        <div class="lista-icon blue">📄</div>
        <div class="lista-info">
          <div class="lista-nombre">Ecografia_doppler_venoso.pdf</div>
          <div class="lista-meta">🔊 Ecografía · 10/07/26 · Dra. Martínez</div>
        </div>
        <div class="lista-actions">
          <button class="ficha-btn ficha-btn-xs ficha-btn-outline" title="Ver">👁️</button>
          <button class="ficha-btn ficha-btn-xs ficha-btn-outline" title="Descargar">⬇️</button>
        </div>
      </div>
    </div>
  </div>

</div><!-- /padding -->

</body>
</html>