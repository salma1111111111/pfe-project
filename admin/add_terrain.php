<?php
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';


//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = array_filter($_POST);

    //Insert timestamp
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
    $db = getDbInstance();

    $last_id = $db->insert('terrain', $data_to_store);

    if ($last_id) {
        $_SESSION['success'] = "success!";
        header('location: terrains.php');
        exit();
    } else {
        echo 'insert failed: ' . $db->getLastError();
        exit();
    }
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">Ajouter terrain</h2>
        </div>

    </div>
    <form class="form" action="" method="post" id="customer_form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nom">Nom *</label>
            <input type="text" name="nom" value="<?php echo htmlspecialchars($edit ? $customer['f_name'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nom" class="form-control" required="required" id="nom">
        </div>

        <div class="form-group text-center">
            <label></label>
            <button type="submit" class="btn btn-warning">Save <span class="glyphicon glyphicon-send"></span></button>
        </div>
    </form>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        $("#customer_form").validate({
            rules: {
                f_name: {
                    required: true,
                    minlength: 3
                },
                l_name: {
                    required: true,
                    minlength: 3
                },
            }
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>