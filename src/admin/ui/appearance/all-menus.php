<?php if (!defined('SCRIPTLOG')) exit(); ?>
 <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=(isset($pageTitle)) ? $pageTitle : ""; ?>
        <small>
        <a href="index.php?load=menu&action=newMenu&Id=0" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Add New</a>
        </small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php?load=dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="index.php?load=menu">All Menus</a></li>
        <li class="active">Data Menu</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
         <div class="col-xs-12">
         <?php 
         if (isset($errors)) :
         ?>
         <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h2><i class="icon fa fa-ban"></i> Error!</h2>
           <?php 
              foreach ($errors as $e) :
                echo $e;
              endforeach;
           ?>
          </div>
         <?php 
         endif;
         ?>
         
         <?php 
         if (isset($status)) :
         ?>
         <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h2><i class="icon fa fa-check"></i> Success!</h2>
           <?php 
              foreach ($status as $s) :
                echo $s;
              endforeach;
           ?>
          </div>
         <?php 
         endif;
         ?>
         
            <div class="box box-primary">
               <div class="box-header with-border">
                <h2 class="box-title">
              <?=(isset($menusTotal)) ? $menusTotal : 0; ?> 
               Menu<?=($menusTotal != 1) ? 's' : ''; ?>
               in Total  
              </h2>
               </div>
              <!-- /.box-header -->
              
              <div class="box-body">
               <table id="scriptlog-table" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Menu</th>
                  <th>Parent</th>
                  <th>Link</th>
                  <th>Status</th>
                  <th>Edit</th>
                  <th>Delete</th>
                </tr>
                </thead>
                <tbody>
                 <?php 
                   if (is_array($menus)) : 
                   $no = 0;
                   $parents = null;
                   foreach ($menus as $m => $menu) :

                    $parents = nav_parent($menu['parent_id']);

                  ?>
              
                    <tr>
                      
                       <td><?= htmlspecialchars($menu['menu_label']); ?></td>
                       <td>
                       <?php
                           foreach ($parents as $parent) :

                            echo $parent['menu_label'];
    
                            endforeach;
                       ?>
                       </td>
                       <td><?= htmlspecialchars($menu['menu_link']); ?></td>
                       <td>
                        <?php if ($menu['menu_status'] === 'N') :?> 
                           disabled
                        <?php 
                           else :
                        ?>
                            enabled
                        <?php endif; ?>
                       </td>
                       <td>
                       <a href="index.php?load=menu&action=editMenu&menuId=<?= htmlspecialchars((int)$menu['ID']);?>" class="btn btn-warning" title="Edit menu">
                       <i class="fa fa-pencil fa-fw"></i> </a>
                       </td>
                       <td>
                       <a href="javascript:deleteMenu('<?= abs((int)$menu['ID']); ?>', '<?= $menu['menu_label']; ?>')" class="btn btn-danger" title="Deactivate menu">
                       <i class="fa fa-trash-o fa-fw"></i> </a>  
                       </td>
                    
                    </tr>
                
                <?php 
                  $no++;
                endforeach;
                endif; 
                ?>
                
                </tbody>
                <tfoot>
                <tr>
                  <th>Menu</th>
                  <th>Parent</th>
                  <th>Link</th>
                  <th>Status</th>
                  <th>Edit</th>
                  <th>Delete</th>
                </tr>
                </tfoot>
              </table>
              </div>
                  <!-- /.box-body -->
            </div>
               <!-- /.box -->
         </div>
            <!-- /.col-xs-12 -->
      </div>
           <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<script type="text/javascript">
  
  function deleteMenu(id, menu)
  {
	  if (confirm("Are you sure want to delete menu '" + menu + "'"))
	  {
	  	window.location.href = 'index.php?load=menu&action=deleteMenu&menuId=' + id;
	  }
  }
  
</script>