<footer id="footer">
    Copyright <a href="http://webukatu.com/">ウェブカツ!!WEBサービス部</a>. All Rights Reserved.
</footer>

<script src="js/vendor/jquery-3.6.3.min.js"></script>
<script>
    $(function() {
        var $ftr = $('#footer');
        if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
            $ftr.attr({
                'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'
            });
        }
        // メッセージの表示
        var $jsShowMsg = $('#js-show-msg');
        var msg = $jsShowMsg.text();
        if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
            $jsShowMsg.slideToggle('slow');
            setTimeout(function(){ $jsShowMsg.slideToggle('slow') }, 5000);
        }

        //画像のライブプレビュー
        var $dropArea = $('.area-drop');
        var $fileInput = $('.input-file');
        $dropArea.on('dragover', function(e) {
            e.stopPropagation();
            e.preventDefault();
            $(this).css('border', '3px #ccc dashed');
        });
        $dropArea.on('dragleave', function(e){
            e.stopPropagation();
            e.preventDefault();
            $(this).css('border', 'none');
        });
        $fileInput.on('change', function(e){
            $dropArea.css('border', 'none');
            var file = this.files[0], // 2.files配列にファイルが入っています
                $img = $(this).siblings('.prev-img'), // 3.jQueryのsiblingsメソッドで兄弟のimgを取得
                fileReader = new FileReader(); // 4. ファイルを読み込むFileReaderオブジェクト

            // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
            fileReader.onload = function(event) {
                //読み込んだデータをimgに設定
                $img.attr('src', event.target.result).show();
            };

            // 6.画像読み込み
            fileReader.readAsDataURL(file); 
        });

        //テキストエリアにカウント
        var $countUp = $('#js-count'),
            $countView = $('#js-count-view');
        $countUp.on('keyup', function(e){
            $countView.html($(this).val().length);
        });

        //画像の切り替え
        var $switchImgSubs = $('.js-switch-img-sub'),
            $switchImgMain = $('#js-switch-img-main');
        $switchImgSubs.on('click', function(e){
            $switchImgMain.attr('src', $(this).attr('src'));
        });

        // お気に入り登録・削除
        var $like,
            likeProductId;
        $like = $('.js-click-like') || null; // nullというのはnull値という値で、「変数の中身はからですよ」と明示するために使う値
        likeProductId = $like.data('productid') || null;
        // 変数の0はfalseと判定されてしまうため,product_idが0の場合もありえるので、0もtrueとする場合にはundefinedとnullを判定する
        if(likeProductId !== undefined && likeProductId !== null){
            $like.on('click', function(){
                var $this = $(this);
                $.ajax({
                    type: "POST",
                    url: "ajaxLike.php",
                    data: { productId : likeProductId }
                }).done(function(data){
                    console.log('Ajax Success');
                    // クラス属性をtoggleでつけ外しする
                    $this.toggleClass('active');
                }).fail(function(msg){
                    console.log('Ajax Error');
                });
            })
        }
    });
    function onSignIn(googleUser){
        var id_token = googleUser.getAuthResponse().id_token;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'auth.php');
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.onload = function() {
            console.log('Sign in as: ' + xhr.responseText);
        };
        xhr.send('idtoken='. id_token);
    }
</script>
</body>

</html>