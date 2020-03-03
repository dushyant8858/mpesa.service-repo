<!DOCTYPE html>
<html lang="en" itemscope itemtype="https://schema.org/Article">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=yes">
    <link rel="canonical" href="https://www.tooplate.com/live/2056-simple-life" />
    <title>SMS BuuPass - Live View</title>
    <meta name="description" content="Live viewing of Simple Life HTML CSS template - Tooplate.com">
    <meta name="author" content="tooplate">

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <link href="{{url('assets/css/app.css')}}" rel="stylesheet">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->

    <script src="//cdnjs.cloudflare.com/ajax/libs/gsap/latest/TweenMax.min.js"></script>
</head>

<body>
    <div class="accordion">
        <h2 class="accordion__title"></h2>
        <ul class="accordion__list">
            <li class="accordion__item">
                <div class="accordion__itemTitleWrap">
                    <h3 class="accordion__itemTitle">SEND MESSAGE</h3>
                    <div class="accordion__itemIconWrap"><svg viewBox="0 0 24 24">
                            <polyline fill="none" points="21,8.5 12,17.5 3,8.5 " stroke="#FFF" stroke-miterlimit="10"
                                stroke-width="2" /></svg></div>
                </div>
                <div class="accordion__itemContent">
                    <code>
                        POST /cgi-bin/process.cgi HTTP/1.1<br>
User-Agent: Mozilla/4.0 (compatible; MSIE5.01; Windows NT)<br>
Host: https://sms.buupass.com<br>
Content-Type: application/x-www-form-urlencoded<br>
Content-Length: length<br>
Accept-Language: en-us<br>
Connection: Keep-Alive
                    </code>
                </div>
            </li>
            <li class="accordion__item">
                <div class="accordion__itemTitleWrap">
                    <h3 class="accordion__itemTitle">GROUPS</h3>
                    <div class="accordion__itemIconWrap"><svg viewBox="0 0 24 24">
                            <polyline fill="none" points="21,8.5 12,17.5 3,8.5 " stroke="#FFF" stroke-miterlimit="10"
                                stroke-width="2" /></svg></div>
                </div>
                <div class="accordion__itemContent">
                    <code>
                        GET /groups HTTP/1.1<br>
                        User-Agent: Mozilla/4.0 (compatible; MSIE5.01; Windows NT)<br>
                        Host: https://sms.buupass.com<br>
                        Accept-Language: en-us<br>
                        Accept-Encoding: gzip, deflate<br>
                        Connection: Keep-Alive
                    </code>

                </div>
            </li>
            <li class="accordion__item">
                <div class="accordion__itemTitleWrap">
                    <h3 class="accordion__itemTitle">TEMPLATES</h3>
                    <div class="accordion__itemIconWrap"><svg viewBox="0 0 24 24">
                            <polyline fill="none" points="21,8.5 12,17.5 3,8.5 " stroke="#FFF" stroke-miterlimit="10"
                                stroke-width="2" /></svg></div>
                </div>
                <div class="accordion__itemContent">
                    <code>
                        GET /templates HTTP/1.1<br>
                        User-Agent: Mozilla/4.0 (compatible; MSIE5.01; Windows NT)<br>
                        Host: https://sms.buupass.com<br>
                        Accept-Language: en-us<br>
                        Connection: Keep-Alive
                    </code>

                </div>
            </li>
            <li class="accordion__item">
                <div class="accordion__itemTitleWrap">
                    <h3 class="accordion__itemTitle">CAMPAIGNS</h3>
                    <div class="accordion__itemIconWrap"><svg viewBox="0 0 24 24">
                            <polyline fill="none" points="21,8.5 12,17.5 3,8.5 " stroke="#FFF" stroke-miterlimit="10"
                                stroke-width="2" /></svg></div>
                </div>
                <div class="accordion__itemContent">
                    <code>
                        GET /campaigns HTTP/1.1<br>
                        User-Agent: Mozilla/4.0 (compatible; MSIE5.01; Windows NT)<br>
                        Host: https://sms.buupass.com<br>
                        Accept-Language: en-us<br>
                        Connection: Keep-Alive
                    </code>

                </div>
            </li>
        </ul>
    </div>
    <script src="{{url('assets/js/app.js')}}"></script>
</body>

</html>
