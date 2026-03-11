<div class="container is-fluid mb-6">
    <h1 class="title">Cuenta Corriente</h1>
    <h2 class="subtitle">Clientes con saldo pendiente</h2>
</div>

<div class="container pb-6 pt-6">
    <div class="table-container">
        <div class="table-responsive"><table class="table is-bordered is-striped is-hoverable is-fullwidth">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Saldo</th>
                    <th>Ver movimientos</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $cc=$insLogin->seleccionarDatos("Normal","cuenta_corriente cc INNER JOIN cliente c ON cc.cliente_id=c.cliente_id","*",0);

                    if($cc->rowCount()>0){
                        while($row=$cc->fetch()){
                            echo '
                            <tr>
                                <td>'.$row['cliente_nombre'].' '.$row['cliente_apellido'].'</td>
                                <td>'.MONEDA_SIMBOLO.number_format($row['saldo'],2).'</td>
                                <td class="has-text-centered">
                                    <a href="'.APP_URL.'ccMovimientos/'.$row['cliente_id'].'/" class="button is-link is-small">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>';
                        }
                    }else{
                        echo '<tr><td colspan="3" class="has-text-centered">No hay cuentas pendientes</td></tr>';
                    }
                ?>
            </tbody>
        </table></div>
    </div>
</div>