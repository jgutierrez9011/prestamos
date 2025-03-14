<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Botones en Celda de Tabla</title>
</head>
<body>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Celda con Botones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <!-- Agrupación de botones con tamaño pequeño -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="tooltip" data-placement="top" title="Botón 1">Botón 1</button>
                        <button type="button" class="btn btn-sm btn-secondary popConfirm" data-toggle="confirmation" data-placement="top" data-title="¿Estás seguro?" data-content="Esta acción no se puede deshacer." data-toggle="tooltip" data-placement="top" title="Botón 2">Botón 2</button>
                        <button type="button" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Botón 3">Botón 3</button>
                        <button type="button" class="btn btn-sm btn-danger" data-toggle="tooltip" data-placement="top" title="Botón 4">Botón 4</button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Scripts de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Inicializa popConfirm
        $(document).ready(function () {
            $('.popConfirm').confirmation();
        });

        // Inicializa tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>

