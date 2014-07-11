<html>
<head></head>
<body>
<script>
    if (window.self !== window.top) {
        var wallpaperDocument = parent.document;
        var wallpaperWindow = parent.window;
    } else {
        var wallpaperDocument = document;
        var wallpaperWindow = window;
    }

    var maindiv = wallpaperDocument.createElement('div');
    maindiv.innerHTML = '<div id="r8-ads-html">' +
        '<style>body { background: url("%%FILE:JPG1%%") #000000 !important; background-size: 2560px auto !important; background-repeat: no-repeat !important; background-position: top center !important; }' +
        '#r8-ads-layer { display: block; position: absolute; top: 0px; left:0px; width: 100%; height: 800px; z-index: −10; cursor: pointer; }' +
        '#r8-video-layer { width: 1060px; margin: auto; }' +
        '#r8-video-player { margin: 10px 0px 0px 20px;cursor:pointer;}' +
        'header{background:white;margin: auto;}' +
        '.line{background:white; width:1080px !important;}' +
        '#navigation{width:1080px !important;}' +
        '.right{margin-right: 5px;}' +
        'footer{background:rgb(51, 51, 51) !important;}' +
        '.left{margin-left: 5px;}.pub728{margin-left: 5px;}</style>' +
        '</div>' +
        '<div id="r8-video-layer">' +
            '<video id="r8-video-player" width="360" height="240" preload="true" autoplay="true" volume="0" loop>' +
                '<source src="http://www.rock8.com/titanfall.mp4" type="video/mp4" />' +
                '<source src="http://www.rock8.com/titanfall.webm" type="video/webm" />' +
            '</video>' +
        '</div>' +
        '<a target="_blank" id="r8-ads-layer" href="%%CLICK_URL_UNESC%%"></a>';

    wallpaperDocument.body.appendChild(maindiv);
    var video = wallpaperDocument.getElementById('r8-video-player');
    var ad = wallpaperDocument.getElementById('r8-ads-layer');
    video.volume = 0;
    ad.addEventListener('mouseover',function(){
        video.volume=.8;video.muted=false;console.log('over');
    });
    ad.addEventListener('mouseleave',function(){
        video.volume=0;video.muted=true;console.log('out');
    });
    video.addEventListener('click',function(){
        document.getElementById('r8-ads-layer').click();
    });
</script>

</body>
</html>