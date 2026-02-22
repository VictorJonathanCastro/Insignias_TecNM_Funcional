<?php
session_start();
require_once 'conexion.php';
require_once 'verificar_sesion.php';

// Verificar sesión y permisos
if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['error' => 'No autorizado']));
}

verificarRoles(['Administrador', 'Admin', 'SuperUsuario']);

$tabla = $_GET['tabla'] ?? '';
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? 0;

try {
    if ($tabla === 'destinatario' && $accion === 'listar') {
        header('Content-Type: text/html; charset=utf-8');
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
        header('Content-Type: application/json; charset=utf-8');
        $stmt = $conexion->prepare("SELECT * FROM destinatario WHERE ID_destinatario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_assoc());
    } elseif ($tabla === 'T_insignias' && $accion === 'listar') {
        header('Content-Type: text/html; charset=utf-8');
        
        // Verificar estructura de tipo_insignia
        $check_id = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'id'");
        $tiene_id = ($check_id && $check_id->num_rows > 0);
        $campo_id_tipo = $tiene_id ? 'id' : 'ID_tipo';
        
        $check_nombre = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Nombre_Insignia'");
        $tiene_nombre_insignia = ($check_nombre && $check_nombre->num_rows > 0);
        $campo_nombre_tipo = $tiene_nombre_insignia ? 'Nombre_Insignia' : 'Nombre_ins';
        
        $sql = "SELECT t.*, ti.$campo_nombre_tipo as nombre_tipo FROM T_insignias t LEFT JOIN tipo_insignia ti ON t.Tipo_Insignia = ti.$campo_id_tipo ORDER BY t.id DESC LIMIT 100";
        $result = $conexion->query($sql);
        
        $html = '<table><thead><tr><th>ID</th><th>Tipo</th><th>Programa</th><th>Descripción</th><th>Acciones</th></tr></thead><tbody>';
        if ($result && $result->num_rows > 0) {
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
        } else {
            $html .= '<tr><td colspan="5" style="text-align: center; padding: 20px;">No hay registros disponibles</td></tr>';
        }
        $html .= '</tbody></table>';
        echo $html;
    } elseif ($tabla === 'T_insignias' && $accion === 'obtener') {
        header('Content-Type: application/json; charset=utf-8');
        $stmt = $conexion->prepare("SELECT * FROM T_insignias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_assoc());
    } elseif ($tabla === 'categorias' && $accion === 'listar') {
        header('Content-Type: text/html; charset=utf-8');
        $check_id = $conexion->query("SHOW COLUMNS FROM cat_insignias LIKE 'id'");
        $tiene_id = ($check_id && $check_id->num_rows > 0);
        $campo_id = $tiene_id ? 'id' : 'ID_cat';
        
        $result = $conexion->query("SELECT * FROM cat_insignias ORDER BY $campo_id DESC");
        $html = '<table><thead><tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr></thead><tbody>';
        if ($result && $result->num_rows > 0) {
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
        } else {
            $html .= '<tr><td colspan="3" style="text-align: center; padding: 20px;">No hay registros disponibles</td></tr>';
        }
        $html .= '</tbody></table>';
        echo $html;
    } elseif ($tabla === 'subcategorias' && $accion === 'listar') {
        header('Content-Type: text/html; charset=utf-8');
        $check_id = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'id'");
        $tiene_id = ($check_id && $check_id->num_rows > 0);
        $campo_id = $tiene_id ? 'id' : 'ID_tipo';
        
        $check_nombre = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Nombre_Insignia'");
        $tiene_nombre_insignia = ($check_nombre && $check_nombre->num_rows > 0);
        $campo_nombre = $tiene_nombre_insignia ? 'Nombre_Insignia' : 'Nombre_ins';
        
        // Verificar estructura de cat_insignias para el JOIN
        $check_cat_id = $conexion->query("SHOW COLUMNS FROM cat_insignias LIKE 'id'");
        $tiene_cat_id = ($check_cat_id && $check_cat_id->num_rows > 0);
        $campo_cat_id = $tiene_cat_id ? 'id' : 'ID_cat';
        
        $sql = "SELECT ti.*, ci.Nombre_cat FROM tipo_insignia ti LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.$campo_cat_id ORDER BY ti.$campo_id DESC LIMIT 100";
        $result = $conexion->query($sql);
        
        $html = '<table><thead><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Acciones</th></tr></thead><tbody>';
        if ($result && $result->num_rows > 0) {
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
        } else {
            $html .= '<tr><td colspan="4" style="text-align: center; padding: 20px;">No hay registros disponibles</td></tr>';
        }
        $html .= '</tbody></table>';
        echo $html;
    } elseif ($tabla === 'certificados' && $accion === 'listar') {
        header('Content-Type: text/html; charset=utf-8');
        echo '<div style="text-align: center; padding: 24px; color: #6c757d;">Gestión de certificados. Use este espacio para listar certificados cuando esté configurado.</div>';
    } elseif ($tabla === 'insigniasotorgadas' && $accion === 'listar') {
        header('Content-Type: text/html; charset=utf-8');
        $result = @$conexion->query("SELECT io.id, io.clave_insignia, d.Nombre_Completo, io.fecha_otorgamiento FROM insigniasotorgadas io LEFT JOIN destinatario d ON io.destinatario_id = d.ID_destinatario ORDER BY io.id DESC LIMIT 100");
        if ($result && $result->num_rows > 0) {
            $html = '<table><thead><tr><th>ID</th><th>Clave insignia</th><th>Destinatario</th><th>Fecha otorgamiento</th></tr></thead><tbody>';
            while ($row = $result->fetch_assoc()) {
                $html .= '<tr><td>' . htmlspecialchars($row['id'] ?? '') . '</td><td>' . htmlspecialchars($row['clave_insignia'] ?? '') . '</td><td>' . htmlspecialchars($row['Nombre_Completo'] ?? '') . '</td><td>' . htmlspecialchars($row['fecha_otorgamiento'] ?? '') . '</td></tr>';
            }
            $html .= '</tbody></table>';
            echo $html;
        } else {
            echo '<div style="text-align: center; padding: 24px; color: #6c757d;">No hay insignias otorgadas registradas.</div>';
        }
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
