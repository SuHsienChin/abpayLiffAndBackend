<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>遊戲下單</title>
    <!-- 引入 Bootstrap 的 CSS 檔案 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>測試FLEX</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 引入 Bootstrap 的 JavaScript 檔案（注意順序：先引入 jQuery，再引入 Bootstrap 的 JS） -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <script>
        const flexMessage = {
            type: 'flex',
            altText: '這是一個 Flex Message 範例',
            contents: {
                type: 'bubble',
                header: {
                    type: 'box',
                    layout: 'vertical',
                    contents: [
                        {
                            type: 'text',
                            text: '標題',
                            weight: 'bold',
                            size: 'xl'
                        }
                    ]
                },
                hero: {
                    type: 'image',
                    url: 'https://example.com/image.jpg',
                    size: 'full'
                },
                body: {
                    type: 'box',
                    layout: 'vertical',
                    contents: [
                        {
                            type: 'text',
                            text: '這是內文描述',
                            wrap: true
                        },
                        {
                            type: 'separator',
                            margin: 'md'
                        },
                        {
                            type: 'box',
                            layout: 'vertical',
                            margin: 'lg',
                            spacing: 'sm',
                            contents: [
                                {
                                    type: 'box',
                                    layout: 'baseline',
                                    spacing: 'sm',
                                    contents: [
                                        {
                                            type: 'text',
                                            text: '價格',
                                            color: '#aaaaaa',
                                            size: 'sm',
                                            flex: 1
                                        },
                                        {
                                            type: 'text',
                                            text: '$100',
                                            wrap: true,
                                            color: '#666666',
                                            size: 'sm',
                                            flex: 4
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                },
                footer: {
                    type: 'box',
                    layout: 'vertical',
                    contents: [
                        {
                            type: 'button',
                            style: 'primary',
                            color: '#ffffff',
                            height: 'sm',
                            action: {
                                type: 'uri',
                                label: '立即購買',
                                uri: 'https://example.com/buy'
                            }
                        }
                    ]
                }
            }
        };

        // 回覆 Flex Message
        event.reply(flexMessage);
    </script>
</body>

</html>