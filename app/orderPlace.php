<!-- This is temporary file until payment gateway is set -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plate order</title>
    <style>
        html,
        body {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            user-select: none;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .main {
            height: 50%;
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .enrollBtn {
            background-color: #333743;
            border-radius: 20px;
            border: none;
            height: 40px;
            width: 120px;
            color: white;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .enrollBtn:hover {
            background-color: #505566;
        }

        .enrollBtn:active {
            background-color: #202229;
        }
    </style>
</head>

<body>
    <div class="main">
        <h3>Place order for <?php if (isset($_GET['name'])) {
                                echo $_GET['name'];
                            } else {
                                echo "course";
                            } ?></h3>
        <button class="enrollBtn">Place Order</button>
    </div>
    <script src="../js/call.js"></script>
    <script>
        const btnEnroll = document.querySelector(".enrollBtn");
        btnEnroll.addEventListener('click', async () => {
            PlaceOrder();
        });

        async function PlaceOrder() {
            let url = `/orders/add`;
            const body = JSON.stringify({
                user: "<?php echo $_GET['user']; ?>",
                course: "<?php echo $_GET['course']; ?>",
                payment: -1,
            });
            const response = await CallApi(url, body);
            if (response) {
                if (response.data.message !== null) {
                    alert(response.data.message);
                }

                if(response.status === 200){
                    history.back();
                }
            }
        }
    </script>
</body>
</html>