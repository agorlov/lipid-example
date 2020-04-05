/**
 * Textarea-uploader
 *
 * Attempt to formulate the application in the form of objects.
 * Previous version in pasteimage.js
 *
 * @link https://github.com/agorlov/textarea-uploader
 * @author Alexandr Gorlov
 */


/**
 * Event
 *
 * Example:
 * ```
 * new AGEvent('paste', document.getElementById('body')).
 *     add(event => { console.log("The event happened.") });
 * ```
 *
 * @author Alexandr Gorlov
 */
class AGEvent { // implements AGEvent
    constructor(name, elm)
    {
        this.name = name;
        this.elm = elm;
    }

    add(func)
    {
        this.elm.addEventListener(this.name, func);
    }
}

/**
 * Event wrap
 */
class AGEventWrap { // implements AGEvent
    constructor(AGEvent)
    {
        this.orig = AGEvent;
    }

    add(func)
    {
        this.orig.add(func);
    }
}

/**
 * Event from textarea, when cursor is in.
 */
class AGEventFromTextarea { // implements AGEvent
    constructor(AGEvent, elm)
    {
        this.orig = AGEvent;
        this.elm = elm;
    }

    add(func)
    {
        this.orig.add(
            (event) => {
                if (event.target !== this.elm) {
                    return;
                }
                if (document.activeElement !== this.elm) {
                    return;
                }
                func.call(this, event);
            }
        );
    }
}

/**
 * Paste event containing image from clipboard
 */
class AGEventClipbContainsImage { // implements AGEvent
    constructor(AGEvent)
    {
        this.orig = AGEvent;
    }

    add(func)
    {
        this.orig.add(
            (event) => {
                if (! event.clipboardData) {
                    return;
                }
                let items = event.clipboardData.items;
                if (items[0].type.indexOf("image") !== -1) {
                    func.call(this, event);
                }
            }
        );
    }
}


/**
 * Textarea paste event (composed object)
 *
 * When textarea in focus (cursor is in area).
 */
class AGEventTextareaPaste extends AGEventWrap {
    constructor(elm)
    {
        super(
            new AGEventClipbContainsImage( // 3. conaining image
                new AGEventFromTextarea(  // 2. pasted to textarea
                    new AGEvent('paste', elm), // 1. paste event
                    elm
                )
            )
        );
    }
}


/**
 * File uploaded to server
 *
 * Sended as binary data.
 */
class FileUploaded {
    constructor(url)
    {
        this.url = url;
    }

    upload(blob, onload)
    {
        let uplReq = new XMLHttpRequest();
        uplReq.open("POST", this.url, true);
        uplReq.setRequestHeader("X-PASTEIMAGE", "1");
        uplReq.send(blob);

        uplReq.onload = event => {
            onload.call(this, event, uplReq.responseText);
        };
    }
}

/**
 * Textarea
 */
class Textarea {
    constructor(elm)
    {
        this.elm = elm;
    }

    put(text)
    {
        this.elm.value = text;
    }

    text()
    {
        return this.elm.value;
    }

    cursorPos()
    {
        return this.elm.selectionStart;
    }

}

/**
 * Textarea with text representing beginnig of upload
 *
 * put loading text: in textarea ![Uploading image.png…]()
 */
class TextareaUploading {
    constructor(Textarea)
    {
        this.txta = Textarea;
        this.uplText = "![Uploading image.png…]()";
    }

    uploading()
    {
        let cursorPos = this.txta.cursorPos();
        let newLine = "\n";
        if (cursorPos === 0) {
            newLine = "";
        } else if (cursorPos > 0 && this.txta.text()[cursorPos - 1] === "\n") {
            newLine = "";
        }

        this.txta.put(
            this.txta.text().substr(0, cursorPos) +
            newLine + this.uplText + "\n" +
            this.txta.text().substr(cursorPos)
        );
    }
}

/**
 * Textarea where "uploading..." text is replaced with image link in markdown format
 */
class TextareaUploadingReplaced { //implements Textarea
    constructor(elm)
    {
        this.elm = elm;
        this.replaceText = "![Uploading image.png…]()";
    }

    put(text)
    {
        let textPos = this.elm.value.indexOf(this.replaceText);
        let textLen = this.replaceText.length;

        if (textPos >= 0) {
            this.elm.value =
                this.elm.value.substr(0, textPos) +
                text +
                this.elm.value.substr(textPos + textLen);
        } else {
            console.log("Text to replace is not found: " + this.replaceText);
        }

        // move cursor right after replaced text
        this.elm.selectionEnd = textPos + text.length + 1;
    }

    text()
    {
        return this.elm.value;
    }

}


/**
 * Textarea Uploader Component
 *
 * It is main()
 */
class AGTextareaUploader {
    constructor(textareaElm, uploadUrl = '/paste')
    {
        this.textareaElm = textareaElm;
        this.uploadUrl = uploadUrl;
    }

    run()
    {
        // Something pasted in textarea
        new AGEventTextareaPaste(
            this.textareaElm
        ).add(
            event => {
                new TextareaUploading(
                    new Textarea(this.textareaElm)
                ).uploading();

                new FileUploaded(this.uploadUrl).upload(
                    event.clipboardData.items[0].getAsFile(),
                    (event, link) => {
                        new TextareaUploadingReplaced(
                            this.textareaElm
                        ).put("![image](" + link + ")");
                    }
                );
            }
        );
    }
}
