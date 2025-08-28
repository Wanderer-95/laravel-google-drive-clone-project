export function isImage(file) {
    return /^image\/\w+$/.test(file.mime);
}

export function isPdf(file) {
    return [
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'application/vnd.pdf',
        'text/pdf',
        'text/x-pdf'
    ].includes(file.mime);
}

export function isWord(file) {
    return [
        'application/msword',
        'application/doc',
        'application/vnd.ms-word',
        'application/vnd.ms-word.document.macroenabled.12',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template',  // .dotx
        'application/x-msword'
    ].includes(file.mime);
}

export function isExcel(file) {
    return [
        'application/vnd.ms-excel',
        'application/msexcel',
        'application/x-msexcel',
        'application/x-ms-excel',
        'application/x-excel',
        'application/x-dos_ms_excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template', // .xltx
        'application/vnd.ms-excel.sheet.macroenabled.12'
    ].includes(file.mime);
}

export function isText(file) {
    return [
        'text/plain',
        'text/x-log',
        'text/csv',
        'text/tab-separated-values',
        'application/txt',
        'application/text',
        'application/x-text'
    ].includes(file.mime);
}

export function isZip(file) {
    return [
        'application/zip',
        'application/x-zip-compressed',
        'multipart/x-zip',
        'application/x-compressed'
    ].includes(file.mime);
}

export function isAudio(file) {
    return /^audio\/[\w.+-]+$/.test(file.mime);
}

export function isVideo(file) {
    return /^video\/[\w.+-]+$/.test(file.mime);
}

