#!/usr/bin/env node
var http = require('http');  
var qs = require('querystring');  
//http://api.k780.com/?app=weather.today&weaid=shanghai&appkey=25744&sign=7a2ab9870de651b3c575b55d283a61db&format=json 天气
//http://api.k780.com/?app=weather.pm25&weaid=shanghai&appkey=25744&sign=7a2ab9870de651b3c575b55d283a61db&format=json pm2.
var ksign= {
    weaid : "shanghai",
    appkey : 25744,
    sign : "7a2ab9870de651b3c575b55d283a61db",
    format : "json"
}
var type = {
    pm25 : {
        app : "weather.pm25"
    },
    today : {
        app : "weather.today"
    }
}
//获取当前天气情况

function getWeather(opt, cb) {
    var options = {
        hostname : "api.k780.com",
        port : 88,
        path : "/?" + qs.stringify(Object.assign({}, ksign, opt))
    }
    var req = http.request(options, function (res) {  
        res.setEncoding('utf8');  
        res.on('data', function (chunk) {  
            cb(null, chunk);
            //console.log('Body: ' + chunk);  
        });  
    });  
      
    req.on('error', function (e) {  
        cb("error");
        //console.log('problem with request: ' + e.message);  
    });  
    req.end(); 
}

 getWeather(type.today, function(err, obj) {
    if(err) {
        return;
    }
    var pminfo = null;
    var winfo = null;
    var msg = "";
    try {
        winfo = JSON.parse(obj); 
        if(winfo.success == "1" && winfo.result ) {
            winfo = winfo.result;
        }
    } catch(e) {}
    getWeather(type.pm25, function(er, ob) {
        if(!er) {
            try {
                pminfo = JSON.parse(ob);
                if(pminfo.success == "1" && pminfo.result) {
                    pminfo = pminfo.result;
                }
            } catch(e) {}
        }
        if(!winfo) {
            return;
        }
        msg += "室外天气" + winfo.weather_curr;
        msg += ",户外温度" + winfo.temp_curr + "摄氏度";
        //if(pminfo) {
        //    msg += ",空气质量" + pminfo.aqi_levnm + ",P M2.5系数" + pminfo.aqi;
        //}
        //msg += ",最高气温" + winfo.temp_high + "摄氏度" + ",最低气温" + winfo.temp_low + "摄氏度";
        //if(pminfo) {
        //    msg += "," +pminfo.aqi_remark
        //}
        console.log(msg);
    });
 });
 
