<?php

namespace app\controllers;
use app\models\mainModel;

class reportController extends mainModel {

    private function getDateCondition($period){
        if($period=="weekly"){
            return "v.venta_fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()";
        }else{ // monthly
            return "MONTH(v.venta_fecha)=MONTH(CURDATE()) AND YEAR(v.venta_fecha)=YEAR(CURDATE())";
        }
    }

    public function topProductsControlador(){
        $period = isset($_POST['period']) ? $this->limpiarCadena($_POST['period']) : 'weekly';
        $cond = $this->getDateCondition($period);

        $sql = "SELECT p.producto_nombre AS label, SUM(vd.venta_detalle_cantidad) AS value ";
        $sql .= "FROM venta_detalle vd JOIN venta v ON vd.venta_codigo=v.venta_codigo JOIN producto p ON vd.producto_id=p.producto_id ";
        $sql .= "WHERE $cond GROUP BY vd.producto_id ORDER BY value DESC LIMIT 10";

        $res = $this->ejecutarConsulta($sql);
        $data = [];
        while($row=$res->fetch()){
            $data[] = $row;
        }
        return json_encode($data);
    }

    public function topClientsControlador(){
        $period = isset($_POST['period']) ? $this->limpiarCadena($_POST['period']) : 'weekly';
        $cond = $this->getDateCondition($period);

        $sql = "SELECT CONCAT(c.cliente_nombre,' ',c.cliente_apellido) AS label, SUM(v.venta_total) AS value ";
        $sql .= "FROM venta v JOIN cliente c ON v.cliente_id=c.cliente_id ";
        $sql .= "WHERE $cond GROUP BY v.cliente_id ORDER BY value DESC LIMIT 10";

        $res = $this->ejecutarConsulta($sql);
        $data = [];
        while($row=$res->fetch()){
            $data[] = $row;
        }
        return json_encode($data);
    }

    // Totales por día (día, semana, mes)
    public function totalsControlador(){
        $period = isset($_POST['period']) ? $this->limpiarCadena($_POST['period']) : 'weekly';
        if($period=='day'){
            $sql = "SELECT v.venta_fecha AS label, SUM(v.venta_total) AS value FROM venta v WHERE v.venta_fecha=CURDATE() GROUP BY v.venta_fecha";
        }elseif($period=='weekly'){
            $sql = "SELECT v.venta_fecha AS label, SUM(v.venta_total) AS value FROM venta v WHERE v.venta_fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE() GROUP BY v.venta_fecha ORDER BY v.venta_fecha ASC";
        }else{ // month
            $sql = "SELECT v.venta_fecha AS label, SUM(v.venta_total) AS value FROM venta v WHERE MONTH(v.venta_fecha)=MONTH(CURDATE()) AND YEAR(v.venta_fecha)=YEAR(CURDATE()) GROUP BY v.venta_fecha ORDER BY v.venta_fecha ASC";
        }
        $res=$this->ejecutarConsulta($sql);
        $data=[]; while($r=$res->fetch()){ $data[]=$r; }
        return json_encode($data);
    }

    // Ventas por producto (opcional product_id)
    public function salesByProductControlador(){
        $period = isset($_POST['period']) ? $this->limpiarCadena($_POST['period']) : 'monthly';
        $product = isset($_POST['product']) ? intval($_POST['product']) : 0;
        $cond = $this->getDateCondition($period);
        $where = "WHERE $cond";
        if($product>0) $where .= " AND p.producto_id=$product";
        $sql = "SELECT p.producto_nombre AS label, SUM(vd.venta_detalle_cantidad) AS value FROM venta_detalle vd JOIN venta v ON vd.venta_codigo=v.venta_codigo JOIN producto p ON vd.producto_id=p.producto_id $where GROUP BY p.producto_id ORDER BY value DESC LIMIT 50";
        $res=$this->ejecutarConsulta($sql);
        $data=[]; while($r=$res->fetch()){ $data[]=$r; }
        return json_encode($data);
    }

    // Ventas por categoria
    public function salesByCategoryControlador(){
        $period = isset($_POST['period']) ? $this->limpiarCadena($_POST['period']) : 'monthly';
        $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
        $cond = $this->getDateCondition($period);
        $where = "WHERE $cond";
        if($category>0) $where .= " AND c.categoria_id=$category";
        $sql = "SELECT c.categoria_nombre AS label, SUM(vd.venta_detalle_cantidad) AS value FROM venta_detalle vd JOIN venta v ON vd.venta_codigo=v.venta_codigo JOIN producto p ON vd.producto_id=p.producto_id JOIN categoria c ON p.categoria_id=c.categoria_id $where GROUP BY c.categoria_id ORDER BY value DESC";
        $res=$this->ejecutarConsulta($sql);
        $data=[]; while($r=$res->fetch()){ $data[]=$r; }
        return json_encode($data);
    }

    // Historial de ventas por cliente
    public function clientHistoryControlador(){
        $period = isset($_POST['period']) ? $this->limpiarCadena($_POST['period']) : 'monthly';
        $client = isset($_POST['client']) ? intval($_POST['client']) : 0;
        if($client<=0) return json_encode([]);
        $cond = $this->getDateCondition($period);
        $sql = "SELECT v.venta_fecha AS fecha, v.venta_total AS total, v.venta_codigo AS codigo FROM venta v WHERE v.cliente_id=$client AND $cond ORDER BY v.venta_fecha DESC";
        $res=$this->ejecutarConsulta($sql);
        $data=[]; while($r=$res->fetch()){ $data[]=$r; }
        return json_encode($data);
    }

    // Total de compras por cliente (top)
    public function totalPerClientControlador(){
        $period = isset($_POST['period']) ? $this->limpiarCadena($_POST['period']) : 'monthly';
        $cond = $this->getDateCondition($period);
        $sql = "SELECT CONCAT(c.cliente_nombre,' ',c.cliente_apellido) AS label, SUM(v.venta_total) AS value FROM venta v JOIN cliente c ON v.cliente_id=c.cliente_id WHERE $cond GROUP BY v.cliente_id ORDER BY value DESC LIMIT 50";
        $res=$this->ejecutarConsulta($sql);
        $data=[]; while($r=$res->fetch()){ $data[]=$r; }
        return json_encode($data);
    }

    // Productos próximos a agotarse
    public function lowStockControlador(){
        $threshold = isset($_POST['threshold']) ? intval($_POST['threshold']) : 5;
        $sql = "SELECT producto_id AS id, producto_nombre AS nombre, producto_stock_total AS stock, producto_precio_venta AS precio FROM producto WHERE producto_stock_total <= $threshold ORDER BY producto_stock_total ASC LIMIT 100";
        $res=$this->ejecutarConsulta($sql);
        $data=[]; while($r=$res->fetch()){ $data[]=$r; }
        return json_encode($data);
    }

    // Listas para selects
    public function listProductsControlador(){
        $res=$this->ejecutarConsulta("SELECT producto_id AS id, producto_nombre AS text FROM producto ORDER BY producto_nombre ASC");
        $data=[]; while($r=$res->fetch()){ $data[]=$r; }
        return json_encode($data);
    }

    public function listCategoriesControlador(){
        $res=$this->ejecutarConsulta("SELECT categoria_id AS id, categoria_nombre AS text FROM categoria ORDER BY categoria_nombre ASC");
        $data=[]; while($r=$res->fetch()){ $data[]=$r; }
        return json_encode($data);
    }

    public function listClientsControlador(){
        $res=$this->ejecutarConsulta("SELECT cliente_id AS id, CONCAT(cliente_nombre,' ',cliente_apellido) AS text FROM cliente WHERE cliente_id!='1' ORDER BY cliente_nombre ASC");
        $data=[]; while($r=$res->fetch()){ $data[]=$r; }
        return json_encode($data);
    }

}
