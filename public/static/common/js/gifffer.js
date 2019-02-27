var Gifffer = function() {
    var images, d = document,
        ga = "getAttribute",
        sa = "setAttribute";
    images = d && d.querySelectorAll ? d.querySelectorAll("[data-gifffer]") : [];
    var createContainer = function(w, h, el) {
        var con = d.createElement("DIV"),
            cls = el[ga]("class"),
            id = el[ga]("id");
        cls ? con[sa]("class", el[ga]("class")) : null;
        id ? con[sa]("id", el[ga]("id")) : null;
        con[sa]("style", "position:relative;cursor:pointer;width:" + w + "px;height:" + h + "px;");
        var src = el[ga]("data-gifffer");
        var temp = src.split('.');
        if(temp[1]=='gif'){
            var play = d.createElement("DIV");
            play[sa]("style", "width:auto;height:18px;line-height:18px;padding:2px 5px 0px 5px;text-align:center;color:#fff;border-radius:5px;background:rgba(0, 0, 0, 0.3);position:absolute;right:3px;bottom:3px;");
            play.innerHTML = 'GIF图';
            con.appendChild(play);
        }
        el.parentNode.replaceChild(con, el);
        return {
            c: con,
            p: play
        }
    };
    var process = function(el) {
        var url, con, c, w, h, play, gif, playing = false,
            cc, isC;
        url = el[ga]("data-gifffer");
        w = el[ga]("data-gifffer-width");
        h = el[ga]("data-gifffer-height");
        el.style.display = "block";
        c = document.createElement("canvas");
        isC = !!(c.getContext && c.getContext("2d"));
        if (w && h && isC) cc = createContainer(w, h, el);
        el.onload = function() {
            if (isC) {
                w = w || el.width;
                h = h || el.height;
                if (!cc) cc = createContainer(w, h, el);
                con = cc.c;
                play = cc.p;
                con.addEventListener("click", function() {
                    return false;
                    if (!playing) {
                        playing = true;
                        gif = d.createElement("IMG");
                        gif[sa]("style", "width:" + w + "px;height:" + h + "px;");
                        gif[sa]("data-uri", Math.floor(Math.random() * 1e5) + 1);
                        setTimeout(function() {
                            gif.src = url
                        }, 0);
                        con.removeChild(play);
                        con.removeChild(c);
                        con.appendChild(gif)
                    } else {
                        playing = false;
                        con.appendChild(play);
                        con.removeChild(gif);
                        con.appendChild(c);
                        gif = null
                    }
                });
                c.width = w;
                c.height = h;
                c.getContext("2d").drawImage(el, 0, 0, w, h);
                con.appendChild(c)
            }
        };
        el.src = url
    };
    for (var i = 0; i < images.length; i++) process(images[i])
};