<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <title>後台訂單管理</title>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <?php
                        require_once 'menu_temp.php';
                        ?>
                        <!-- 其他菜單項目 -->
                    </ul>
                </div>
            </nav>

            <!-- 主要內容 -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">訂單完成的顯示內容</h1>
                </div>

                <!-- 訂單表格 -->
                <div class="table-responsive">
                    <div class="container mt-5">
                        <h2 class="text-center mb-4">顯示內容</h2>
                        <form action="order_finish_display_messages_data.php" method="post">
                            <?php
                            // 連接資料庫
                            require_once 'databaseConnection.php';
                            // 假设您已经连接到数据库
                            
                            $connection = new DatabaseConnection();
                            $pdo = $connection->connect();
                            try {

                                // 從資料庫獲取內容
                                $query = $pdo->query("SELECT * FROM order_finish_display_messages");
                                $result = $query->fetchAll(PDO::FETCH_ASSOC);

                                // 釋放資料庫連接
                                $conn = null;

                                // 遍歷編輯器標題，並顯示內容
                                $i = 1;
                                foreach ($result as $data) {
                                    echo '<div class="form-group">';
                                    echo '<label for="editor' . ($i) . '">' . $data['title'] . '</label>';
                                    echo '<textarea class="form-control summernote" id="editor' . ($i) . '" name="editor[]">' . json_decode($data['content']) . '</textarea>';
                                    echo '<input type="hidden" id="title' . ($i) . '" name="title[] " value="' . $data['title'] . '" />';
                                    echo '</div>';
                                    $i++;
                                }
                            } catch (PDOException $e) {
                                echo "資料庫連接錯誤：" . $e->getMessage();
                            }
                            ?>


                            <button type="submit" class="btn btn-primary">送出</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


</body>
<script>

</script>

</html>