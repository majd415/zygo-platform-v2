<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Zygo Taxi - تتبع الرحلة</title>
<style>
*\{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#0a0a1a;color:#fff}
#map{width:100%;height:100vh}.info-bar{position:fixed;bottom:0;left:0;right:0;background:rgba(10,10,26,0.95);backdrop-filter:blur(20px);border-radius:24px 24px 0 0;padding:20px 24px 30px;z-index:10;border-top:1px solid rgba(255,255,255,0.08)}
.driver-row{display:flex;align-items:center;gap:14px;margin-bottom:14px}.driver-avatar{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#003384,#0055cc);display:flex;align-items:center;justify-content:center;font-size:22px}
.driver-name{font-weight:700;font-size:16px}.driver-vehicle{opacity:0.5;font-size:12px;margin-top:2px}
.status-badge{display:inline-block;padding:6px 16px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase}
.status-accepted{background:rgba(0,51,132,0.2);color:#4d8eff}.status-arrived{background:rgba(255,165,0,0.2);color:#ffa500}.status-started{background:rgba(0,200,83,0.2);color:#00c853}
.address-row{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:13px;opacity:0.7}.dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}.dot-pickup{background:#4d8eff}.dot-dest{background:#ff6b35}
.ended-overlay{position:fixed;inset:0;background:rgba(10,10,26,0.97);display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:100}
.ended-overlay .icon{font-size:64px;margin-bottom:20px}.ended-overlay h2{font-size:24px;font-weight:800;margin-bottom:8px}.ended-overlay p{opacity:0.5;font-size:14px}
.logo-bar{position:fixed;top:0;left:0;right:0;padding:16px 20px;z-index:10;display:flex;align-items:center;gap:10px;background:linear-gradient(to bottom,rgba(10,10,26,0.8),transparent)}
.logo-bar span{font-weight:900;font-size:18px;letter-spacing:-0.5px}.pulse{animation:pulse 2s infinite}@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}
.loading{position:fixed;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#0a0a1a;z-index:200}
.spinner{width:40px;height:40px;border:3px solid rgba(255,255,255,0.1);border-top-color:#4d8eff;border-radius:50%;animation:spin 0.8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<div id="loading" class="loading"><div class="spinner"></div><p style="margin-top:16px;opacity:0.5;font-size:13px">جاری تحميل الرحلة...</p></div>
<div id="ended" class="ended-overlay" style="display:none"><div class="icon">✅</div><h2>انتهت الرحلة</h2><p>شكراً لاستخدامك Zygo Taxi</p></div>
<div class="logo-bar"><span>🚕 Zygo</span><span id="liveTag" class="pulse" style="font-size:10px;background:rgba(0,200,83,0.2);color:#00c853;padding:4px 10px;border-radius:10px;font-weight:800;letter-spacing:1px">LIVE</span></div>
<div id="map"></div>
<div id="infoBar" class="info-bar" style="display:none">
<div class="driver-row"><div class="driver-avatar">🚄</div><div style="flex:1"><div class="driver-name" id="driverName">---</div><div class="driver-vehicle" id="driverVehicle">---</div></div><span class="status-badge" id="statusBadge">---</span></div>
<div class="address-row"><div class="dot dot-pickup"></div><span id="pickupAddr">---</span></div>
<div class="address-row"><div class="dot dot-dest"></div><span id="destAddr">---</span></div>
</div>
<script>
var params=new URLSearchParams(window.location.search),token=params.get('token'),API='https://zygo-taxi.com/api';
var map,driverMarker,pickupMarker,destMarker,routePath;
if(!token){document.getElementById('loading').style.display='none';document.getElementById('ended').style.display='flex';document.querySelector('#ended h2').textContent='رابط غير صالح';document.querySelector('#ended .icon').textContent='❌';}
function initMap(){map=new google.maps.Map(document.getElementById('map'),{center:{lat:33.5138,lng:36.2765},zoom:14,disableDefaultUI:true,zoomControl:true,styles:[{elementType:'geometry',stylers:[{color:'#1d2c4d'}]},{elementType:'labels.text.fill',stylers:[{color:'#8ec3b9'}]},{elementType:'labels.text.stroke',stylers:[{color:'#1a3646'}]},{featureType:'road',elementType:'geometry',stylers:[{color:'#304a7d'}]},{featureType:'water',elementType:'geometry',Stylers:[{color:'#0e1626'}]}]});if(token)fetchRide();}
async function fetchRide(){try{var res=await fetch(API+'/ride/track/'+token);var data=await res.json();document.getElementById('loading').style.display='none';
if(data.status==='not_found'){document.getElementById('ended').style.display='flex';document.querySelector('#ended h2').textContent='الرحلة غير موجودة';document.querySelector('#ended .icon').textContent='❌';return;}
if(data.status==='ended'){document.getElementById('ended').style.display='flex';return;}
document.getElementById('infoBar').style.display='block';
var badge=document.getElementById('statusBadge'),sm={searching:'جاري البحث',accepted:'في الطريق',arrived:'وصل',started:'جارية'};badge.textContent=sm[data.status]||data.status;badge.className='status-badge status-'+data.status;
document.getElementById('pickupAddr').textContent=data.pickup_address||'---';document.getElementById('destAddr').textContent=data.dropoff_address||'---';
if(data.driver){document.getElementById('driverName').textContent=data.driver.name;document.getElementById('driverVehicle').textContent=data.driver.vehicle.color+' '+data.driver.vehicle.model+' • '+data.driver.vehicle.plate;
var dp={lat:data.driver.lat,lng:data.driver.lng};if(!driverMarker){driverMarker=new google.maps.Marker({position:dp,map:map,icon:{path:google.maps.SymbolPath.FORWARD_CLOSED_ARROW,scale:6,fillColor:'#4d8eff',fillOpacity:1,strokeColor:'#fff',strokeWeight:2,rotation:data.driver.bearing},zIndex:10});}else{driverMarker.setPosition(dp);}}
var pu={lat:data.pickup_lat,lng:data.pickup_lng},de={lat:data.dropoff_lat,lng:data.dropoff_lng};
if(!pickupMarker){pickupMarker=new google.maps.Marker({position:pu,map:map,label:{text:'|߭',fontSize:'20px'}});destMarker=new google.maps.Marker({position:de,map:map,label:{text:'🖠',fontSize:'20px'}});
var b=new google.maps.LatLngBounds();b.extend(pu);b.extend(de);if(data.driver)b.extend({lat:data.driver.lat,lng:data.driver.lng});map.fitBounds(b,80);
new google.maps.DirectionsService().route({origin:pu,destination:de,travelMode:'DRIVING'},function(r,s){if(s==='OK')routePath=new google.maps.DirectionsRenderer({map:map,directions:r,suppressMarkers:true,polylineOptions:{strokeColor:'#4d8eff',strokeWeight:4,strokeOpacity:0.7}});});}
setTimeout(fetchRide,5000);}catch(e){console.error(e);setTimeout(fetchRide,8000);}}
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC5zAXaeoY71nxrFSLqXV5VYzCMCXjd3_Q&callback=initMap"></script>
</body></html>