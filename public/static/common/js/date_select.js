function YYYYMMDDstart(){
    MonHead = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    //先给年下拉框赋内容
    var y  = new Date().getFullYear();
    for (var i = (y-60); i < (y+5); i++) //以今年为准，前100年，后0年
        document.dateForm.YYYY.options.add(new Option(""+ i +"", i));

    //赋月份的下拉框
    for (var i = 1; i < 13; i++){
        var j = i>=10?i:'0'+i;
        document.dateForm.MM.options.add(new Option("" + j + "", i));
    }

    document.dateForm.YYYY.value = y;
    document.dateForm.MM.value = new Date().getMonth() + 1;
    var n = MonHead[new Date().getMonth()];
    var YYYYvalue = document.dateForm.YYYY.options[document.dateForm.YYYY.selectedIndex].value;
    if (new Date().getMonth() ==1 && IsPinYear(YYYYvalue)) n++;
    writeDay(n); //赋日期下拉框
    document.dateForm.DD.value = new Date().getDate();
    select(); //选择已有日期
}

if(document.attachEvent){
    window.attachEvent("onload", YYYYMMDDstart);
}else{
    window.addEventListener('load', YYYYMMDDstart, false);
}

//年发生变化时日期发生变化(主要是判断闰平年)
function YYYYDD(str){
    var MMvalue = document.dateForm.MM.options[document.dateForm.MM.selectedIndex].value;
    if (MMvalue == ""){ var e = document.dateForm.DD; optionsClear(e); return;}
    var n = MonHead[MMvalue - 1];
    if (MMvalue ==2 && IsPinYear(str)) n++;
    writeDay(n)
}

//月发生变化时日期联动
function MMDD(str){
    var YYYYvalue = document.dateForm.YYYY.options[document.dateForm.YYYY.selectedIndex].value;
    if (YYYYvalue == ""){ var e = document.dateForm.DD; optionsClear(e); return;}
    var n = MonHead[str - 1];
    if (str ==2 && IsPinYear(YYYYvalue)) n++;
    writeDay(n)
}

//据条件写日期的下拉框
function writeDay(n){
    var e = document.dateForm.DD; optionsClear(e);
    for (var i=1; i<(n+1); i++){
        var j = i>=10?i:'0'+i;
        e.options.add(new Option(""+ j + "", i));
    }
}

//判断是否闰平年
function IsPinYear(year){
    return(0 == year%4 && (year%100 !=0 || year%400 == 0));
}

//清空option
function optionsClear(e){
    //e.options.length = 1; //保留第一个
    e.options.length = 0;
}

//选中当前的值
function select(){
    $('#dateForm>select').each(function(){
        var val = parseInt($(this).attr('data-val'));
        if(val){
            $(this).children('option').each(function(){
                if(parseInt(this.value)==val){
                    $(this).attr('selected',true);
                }else{
                    $(this).attr('selected',false);
                }
            })
        }
    })
}