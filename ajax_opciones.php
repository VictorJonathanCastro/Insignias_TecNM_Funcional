<?php
session_start();
require_once 'conexion.php';
require_once 'verificar_sesion.php';

// Verificar sesión y permisos
if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['error' => 'No autorizado']));
}

verificarRoles(['Administrador', 'Admin', 'SuperUsuario']);

header('Content-Type: application/json');

$tabla = $_GET['tabla'] ?? '';
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? 0;

try {
    if ($tabla === 'destinatario' && $accion === 'listar') {
        $result = $conexion->query("SELECT * FROM destinatario ORDER BY ID_destinatario DESC LIMIT 100");
        $html = '<table><thead><tr><th>ID</th><th>Nombre</th><th>CURP</th><th>Correo</th><th>Matrícula</th><th>Acciones</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['ID_destinatario']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Nombre_Completo'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Curp'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Correo'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Matricula'] ?? '') . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn-accion btn-editar" onclick="mostrarFormDestinatario(\'editar\', ' . $row['ID_destinatario'] . ')">Editar</button>';
            $html .= '<button class="btn-accion btn-eliminar" onclick="eliminarRegistro(\'destinatario\', ' . $row['ID_destinatario'] . ')">Eliminar</button>';
            $html .= '</td></tr>';
        }
        $html .= '</tbody></table>';
        echo $html;
    } elseif ($tabla === 'destinatario' && $accion === 'obtener') {
        $stmt = $conexion->prepare("SELECT * FROM destinatario WHERE ID_destinatario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_assoc());
    } elseif ($tabla === 'T_insignias' && $accion === 'listar') {
        $result = $conexion->query("SELECT t.*, ti.Nombre_Insignia as nombre_tipo FROM T_insignias t LEFT JOIN tipo_insignia ti ON t.Tipo_Insignia = ti.id ORDER BY t.id DESC LIMIT 100");
        $html = '<table><thead><tr><th>ID</th><th>Tipo</th><th>Programa</th><th>Descripción</th><th>Acciones</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['nombre_tipo'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Programa'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars(substr($row['Descripcion'] ?? '', 0, 50)) . '...</td>';
            $html .= '<td>';
            $html .= '<button class="btn-accion btn-editar" onclick="mostrarFormInsignia(\'editar\', ' . $row['id'] . ')">Editar</button>';
            $html .= '<button class="btn-accion btn-eliminar" onclick="eliminarRegistro(\'T_insignias\', ' . $row['id'] . ')">Eliminar</button>';
            $html .= '</td></tr>';
        }
        $html .= '</tbody></table>';
        echo $html;
    } elseif ($tabla === 'T_insignias' && $accion === 'obtener') {
        $stmt = $conexion->prepare("SELECT * FROM T_insignias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_assoc());
    } elseif ($tabla === 'categorias' && $accion === 'listar') {
        $check_id = $conexion->query("SHOW COLUMNS FROM cat_insignias LIKE 'id'");
        $tiene_id = ($check_id && $check_id->num_rows > 0);
        $campo_id = $tiene_id ? 'id' : 'ID_cat';
        
        $result = $conexion->query("SELECT * FROM cat_insignias ORDER BY $campo_id DESC");
        $html = '<table><thead><tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            $id_val = $row[$campo_id];
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($id_val) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Nombre_cat'] ?? '') . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn-accion btn-editar" onclick="editarCategoria(' . $id_val . ', \'' . htmlspecialchars($row['Nombre_cat'] ?? '', ENT_QUOTES) . '\')">Editar</button>';
            $html .= '<button class="btn-accion btn-eliminar" onclick="eliminarRegistro(\'categorias\', ' . $id_val . ')">Eliminar</button>';
            $html .= '</td></tr>';
        }
        $html .= '</tbody></table>';
        echo $html;
    } elseif ($tabla === 'subcategorias' && $accion === 'listar') {
        $check_id = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'id'");
        $tiene_id = ($check_id && $check_id->num_rows > 0);
        $campo_id = $tiene_id ? 'id' : 'ID_tipo';
        
        $check_nombre = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Nombre_Insignia'");
        $tiene_nombre_insignia = ($check_nombre && $check_nombre->num_rows > 0);
        $campo_nombre = $tiene_nombre_insignia ? 'Nombre_Insignia' : 'Nombre_ins';
        
        $result = $conexion->query("SELECT ti.*, ci.Nombre_cat FROM tipo_insignia ti LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.id ORDER BY ti.$campo_id DESC LIMIT 100");
        $html = '<table><thead><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Acciones</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            $id_val = $row[$campo_id];
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($id_val) . '</td>';
            $html .= '<td>' . htmlspecialchars($row[$campo_nombre] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Nombre_cat'] ?? '') . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn-accion btn-editar" onclick="editarSubcategoria(' . $id_val . ', \'' . htmlspecialchars($row[$campo_nombre] ?? '', ENT_QUOTES) . '\', ' . ($row['Cat_ins'] ?? 0) . ')">Editar</button>';
            $html .= '<button class="btn-accion btn-eliminar" onclick="eliminarRegistro(\'subcategorias\', ' . $id_val . ')">Eliminar</button>';
            $html .= '</td></tr>';
        }
        $html .= '</tbody></table>';
        echo $html;
    } else {
        echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
