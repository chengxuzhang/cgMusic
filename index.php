<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-param" content="_csrf">
    <meta name="csrf-token" content="ZFg2RU1URWtJHwc/AA4yBQYQTHIOAAYnKjAbMB15FCkzKkN8KR0wIQ==">
    <title>音乐地带</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="js/jquery.js"></script>
    <script src="js/layui/layui.js"></script>
</head>
<body>

<div class="wrap">
    <div class="container">
        
        <script src="js/id3-minimized.js"></script>
        <link href="css/music.css" rel="stylesheet">
        <div class="site-index">
            <div id="music-box">
                <div id="music-lrc">
                    <div class="innerBox"></div>
                </div>

                <div id="music-list"></div>

                <div id="music">
                    <audio src="#" controls="controls" id="audio"></audio>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $(function(){
                // var timestampStart;
                var timeLen;
                var timer;
                var audio = document.getElementById("audio");
                var mList = [];
                var currentNum = 0;

                // 初始化歌词
                function loadLrc(lrc,ext){
                    clearInterval(timer);
                    $(".innerBox").css("top","0px");
                    $.getJSON('musicLrc.php',{lrc:lrc,ext:ext},function(data){
                        if(data.status == 200){
                            audio.play();
                            // timestampStart = parseInt(Date.parse(new Date())); // 当获取到数据后定义开始时间
                            var content = '';
                            $.each(data.data,function(k,v){
                                content += '<li>'+v.lrc+'</li>';
                            });
                            $(".innerBox").html(content);

                            // 开启计时器 滚动歌词
                            timer = setInterval(function(){
                                if(audio.ended){
                                    if((mList.length-1) > currentNum){
                                        currentNum++;
                                    }else{
                                        currentNum = 0;
                                    }
                                    var musicName = mList[currentNum][ 'filename' ];
                                    var musicExt = mList[currentNum][ 'ext' ];
                                    $("#audio").attr("src","http://cgmusic.com/music/"+musicName+"."+musicExt);
                                    loadLrc(musicName, musicExt);
                                    changeStyle($("#music-list li").eq(currentNum));
                                }
                                if(!audio.paused){
                                    var index = -1;
                                    $.each(data.data,function(m,n){
                                        if(audio.currentTime * 1000 > n.time){
                                            index++;
                                        }else{
                                            return;
                                        }
                                    });
                                    $(".innerBox li").removeClass("current");
                                    $(".innerBox li").eq(index).addClass("current");
                                    $(".innerBox").css("top","-"+index*30+"px");
                                }
                            },10);
                        }else{
                            audio.play();
                            $(".innerBox").html('<li>'+data.message+'</li>');

                            // 开启计时器 滚动歌词
                            timer = setInterval(function(){
                                if(audio.ended){
                                    if((mList.length-1) > currentNum){
                                        currentNum++;
                                    }else{
                                        currentNum = 0;
                                    }
                                    var musicName = mList[currentNum][ 'filename' ];
                                    var musicExt = mList[currentNum][ 'ext' ];
                                    $("#audio").attr("src","http://cgmusic.com/music/"+musicName+"."+musicExt);
                                    loadLrc(musicName, musicExt);
                                    changeStyle($("#music-list li").eq(currentNum));
                                }
                            },10);
                        }
                    })
                }

                /**
                 * 获取音乐列表
                 * @return {[type]} [description]
                 */
                function getMusicList(){
                    $.getJSON('musicList.php',function(data){
                        if(data.status == 200){
                            var content = '';
                            $.each(data.data,function(k,v){
                                content += '<li data-music-name="'+v.filename+'" data-music-ext="'+v.ext+'" data-index="'+k+'">'+v.filename+'</li>';
                                mList[ mList.length ] = v;
                            });
                            $("#music-list").html(content);

                            var musicName = mList[ currentNum ][ 'filename' ];
                            var musicExt = mList[ currentNum ][ 'ext' ];
                            $("#audio").attr("src","http://cgmusic.com/music/"+musicName+"."+musicExt);
                            loadLrc(musicName, musicExt);
                            changeStyle($("#music-list li").eq(currentNum));
                        }
                    })
                }

                // 初始化
                function init(){
                    getMusicList(); // 获取音乐列表
                }

                /*
                function loadFile(file) {
                    // var file = input.files[0],
                    var url = file.urn || file.name;
                    ID3.loadTags(url, function() {
                        // showTags(url);
                        var tags = ID3.getAllTags(url);
                        console.log(tags);
                    },{
                        tags: ["title","artist","album","picture"],
                        dataReader: ID3.FileAPIReader(file)
                    });
                }
                */

                function loadFile(file) {
                    ID3.loadTags(file, function() {
                        var tags = ID3.getAllTags(file);
                        // alert(tags.artist + " - " + tags.title);
                    });
                }

                // loadFile("http://cgmusic.com/aimei.mp3");

                // 改变列表选中的歌曲状态
                function changeStyle(li){
                    $("#music-list li").removeClass("current");
                    li.addClass("current");
                }

                /**
                 * 当列表内容的歌曲被点击的时候触发事件
                 * @param  {[type]} ){                     } [description]
                 * @return {[type]}     [description]
                 */
                $(document).on('click','#music-list li',function(){
                    changeStyle($(this));
                    var musicName = $(this).attr("data-music-name");
                    var musicExt = $(this).attr("data-music-ext");
                    currentNum = parseInt($(this).attr("data-index"));
                    $("#audio").attr("src","http://cgmusic.com/music/"+musicName+"."+musicExt);
                    loadLrc(musicName, musicExt);
                });

                /**
                var hiddenProperty = 'hidden' in document ? 'hidden' :
                    'webkitHidden' in document ? 'webkitHidden' :
                    'mozHidden' in document ? 'mozHidden' :
                    null;
                var visibilityChangeEvent = hiddenProperty.replace(/hidden/i, 'visibilitychange');
                var onVisibilityChange = function(){
                    if (!document[hiddenProperty]) {
                        // timeLen = audio.currentTime * 1000;
                    }else{
                        console.log('页面非激活');
                    }
                }
                document.addEventListener(visibilityChangeEvent, onVisibilityChange);
                */

                init();
            })
        </script>
    </div>
</div>

</body>
</html>
