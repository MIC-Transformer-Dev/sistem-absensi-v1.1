<style media="screen">
.btn-md {
    padding: 1rem 2.4rem;
    font-size: .94rem;
    display: none;
}
.swal2-popup {
    font-family: inherit;
    font-size: 1.2rem;}
</style>
<section class='content' id="demo-content">
    <div class='row'>
        <div class='col-xs-12'>
            <div class='box'>
                <div class='box-header'></div>
                <div class='box-body'>
                    <?php
                    $attributes = array('id' => 'button');
                    echo form_open('scan/cek_id',$attributes);?>
                    <div id="sourceSelectPanel" style="display:none">
                        <label for="sourceSelect">Change video source:</label>
                        <select id="sourceSelect" style="max-width:400px"></select>
                    </div>
                    <div id="refresh" style="display:flex">
                        <video id="video" width="512" height="384" style="border: 1px solid gray"></video>
                        <div id="timer" style="margin:auto; font-size:150px; font-weight: bold; display:none">
                        00:03
                        </div>
                    </div>
                    <ul class="list-unstyled" style="margin-top:9px">
                        <li style="font-size: 150%"><b>Untuk melakukan presensi, dekatkan QR Code ke kamera sampai muncul countdown timer.</b></li>
                        <li style="font-size: 150%"><b>Jauhkan QR code dari kamera dan tunggu 3 detik sampai countdown timer menghilang.</b></li>
                    </ul>
                    <textarea hidden="" name="id_karyawan" id="result" readonly></textarea>
                    <span>  <input type="submit"  id="button" class="btn btn-success btn-md" value="Cek Kehadiran"></span>
                    <?php echo form_close();?>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript" src="<?php echo base_url()?>assets/plugins/zxing/zxing.min.js"></script>
<script type="text/javascript">
    let timer = document.getElementById("timer");
    let startingTime = 2
    let startTimer;
    let startStopTimer = () => {
        if(startTimer){
            clearInterval(startTimer);
            timer.innerHTML = '00:03'
            startingTime = 2
            return startTimer = undefined
        }else{
            return startTimer = setInterval(()=>{
            timer.innerHTML = `00:0${startingTime--}`
            }, 1000)
        }
    }

    function showHideTimer() {
        let x = document.getElementById("timer");
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }
</script>
<script type="text/javascript">
    setInterval(function(){
        window.location.reload();
    }, 1200000);

    window.addEventListener('load', function () {
        let selectedDeviceId;
        let audio = new Audio("assets/audio/beep.mp3");
        let audio2 = new Audio("assets/audio/beep-2.mp3");
        const codeReader = new ZXing.BrowserQRCodeReader();

        console.log('ZXing code reader initialized')
        codeReader.getVideoInputDevices()
        .then((videoInputDevices) => {
            const sourceSelect = document.getElementById('sourceSelect')
            selectedDeviceId = videoInputDevices[0].deviceId
            if (videoInputDevices.length >= 1) {
                videoInputDevices.forEach((element) => {
                    const sourceOption = document.createElement('option')
                    sourceOption.text = element.label
                    sourceOption.value = element.deviceId
                    sourceSelect.appendChild(sourceOption)
                })
                sourceSelect.onchange = () => {
                    selectedDeviceId = sourceSelect.value;
                };
                const sourceSelectPanel = document.getElementById('sourceSelectPanel')
                sourceSelectPanel.style.display = 'block'
            }
            codeReader.decodeFromInputVideoDevice(selectedDeviceId, 'video').then((result) => {
                console.log(result)
                document.getElementById('result').textContent = result.text
                if(result != null){
                    audio2.play();
                    showHideTimer();
                    startStopTimer();
                }
                const video = document.getElementById("video");
                const canvas = document.createElement("canvas");
                // scale the canvas accordingly
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                // draw the video at that frame
                setTimeout(function() {
                    canvas.getContext('2d')
                    .drawImage(video, 0, 0, canvas.width, canvas.height);

                    var formData = new FormData(),
                    uploadedImageName = result.text+String(result.timestamp)+'.png';

                    canvas.toBlob(function (blob) {
                        formData.append('gambar', blob, uploadedImageName);
                        formData.append('id_karyawan', result.text);
                        // console.log(formData);
                        $.ajax({
                            data: formData,
                            type: "POST",
                            url: '<?=base_url("scan/cek_id")?>',
                            processData: false,
                            contentType: false,
                            success: function(response){
                                //if request if made successfully then the response represent the data
                                audio.play();
                                showHideTimer();
                                startStopTimer();
                                window.location.reload();
                            }
                        });
                    });
                }, 3000);

            
                // $('#button').submit();
            }).catch((err) => {
                console.error(err)
                document.getElementById('result').textContent = err
            })
            console.log(`Started continous decode from camera with id ${selectedDeviceId}`)
        })
        .catch((err) => {
            console.error(err)
        })
    })
</script>
