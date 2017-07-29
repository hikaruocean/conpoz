function Validator () {

}

Validator.prototype.valid = function (jqForm) {
    var validator = this;
    var msgAry = new Array();
    jqForm.find(':enabled[data-validation-rules]').each(function (k, v) {
        var _thisElem = $(v);
        var rules = _thisElem.attr('data-validation-rules');
        var ruleAry = rules.split(' ');
        if ($.inArray('required', ruleAry) == -1 && _thisElem.val() == '') {
            return;
        } else {
            $.each(ruleAry, function (k2, v2) {
                switch (v2) {
                    /**
                    * no user assign rule value
                    * */
                    case 'required':
                        if (_thisElem.val() == '') {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'boolean':
                        if (_thisElem.val() != 'true' && _thisElem.val() != 'false') {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'number':
                        var reg = /^-?[0-9]+(\.[0-9]+)?$/;
                        if (!reg.test(_thisElem.val())) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'numeric':
                        var reg = /^[0-9]+$/;
                        if (!reg.test(_thisElem.val())) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'alpha-numeric':
                        var reg = /^[A-Za-z0-9]+$/;
                        if (!reg.test(_thisElem.val())) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'date':
                        var tmpDate = _thisElem.val().split('-');
                        if (tmpDate.length < 2 || tmpDate.length > 3) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        for (var val in tmpDate) {
                            if (!$.isNumeric(val)) {
                                var errMsg = _thisElem.attr('data-err-msg-' + v2);
                                msgAry.push({object: _thisElem, 'errMsg': errMsg});
                                return false
                            }
                        }
                        var y = parseInt(tmpDate[0]);
                        var m = parseInt(tmpDate[1]) - 1;
                        if (tmpDate.length == 2) {
                            var vDate = new Date(y, m, 1);
                            if (vDate.getMonth() + 1 != m || vDate.getFullYear() != y) {
                                var errMsg = _thisElem.attr('data-err-msg-' + v2);
                                msgAry.push({object: _thisElem, 'errMsg': errMsg});
                                return false;
                            }
                        } else {
                            var d = parseInt(tmpDate[2]);
                            var vDate = new Date(y, m, d);
                            if (vDate.getFullYear() != y || vDate.getMonth() != m || vDate.getDate() != d) {
                                var errMsg = _thisElem.attr('data-err-msg-' + v2);
                                msgAry.push({object: _thisElem, 'errMsg': errMsg});
                                return false;
                            }
                        }
                        break;
                    case 'date-time':
                        var tmpDate = _thisElem.val().split(/[-\s:]/);
                        if (tmpDate.length != 6) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        for (var val in tmpDate) {
                            if (!$.isNumeric(val)) {
                                var errMsg = _thisElem.attr('data-err-msg-' + v2);
                                msgAry.push({object: _thisElem, 'errMsg': errMsg});
                                return false
                            }
                        }
                        var y = parseInt(tmpDate[0]);
                        var m = parseInt(tmpDate[1]) - 1;
                        var d = parseInt(tmpDate[2]);
                        var h = parseInt(tmpDate[3]);
                        var i = parseInt(tmpDate[4]);
                        var s = parseInt(tmpDate[5]);
                        var vDate = new Date(y, m, d, h, i, s);
                        if (vDate.getFullYear() != y || vDate.getMonth() != m || vDate.getDate() != d || vDate.getHours() != h || vDate.getMinutes() != i || vDate.getSeconds() != s) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'ip':
                        var ipAry = _thisElem.val().split('.');
                        if (ipAry.length != 4) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        $.each(ipAry, function (k, v) {
                            if (v < 0 || v > 255) {
                                var errMsg = _thisElem.attr('data-err-msg-' + v2);
                                msgAry.push({object: _thisElem, 'errMsg': errMsg});
                                return false;
                            }
                        });
                        break;
                    case 'email':
                        if (!validator.isRFC822ValidEmail(_thisElem.val())) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'tel':
                        var reg = /^(\([0-9]+\))?[0-9]+(-[0-9]+)*(#[0-9]+)?$/;
                        if (!reg.test(_thisElem.val())) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    /**
                    * user assign rule value
                    * */
                    case 'max-length':
                        var vData = _thisElem.attr('data-' + v2);
                        if (!vData) {
                            break;
                        }
                        if (validator.utf8len(_thisElem.val()) > parseInt(vData)) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'min-length':
                        var vData = _thisElem.attr('data-' + v2);
                        if (!vData) {
                            break;
                        }
                        if (validator.utf8len(_thisElem.val()) < parseInt(vData)) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'max-byte':
                        var vData = _thisElem.attr('data-' + v2);
                        if (!vData) {
                            break;
                        }
                        if (_thisElem.val().length > parseInt(vData)) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'mix-byte':
                        var vData = _thisElem.attr('data-' + v2);
                        if (!vData) {
                            break;
                        }
                        if (_thisElem.val().length < parseInt(vData)) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'compare-with':
                        var vData = _thisElem.attr('data-' + v2);
                        if (!vData) {
                            break;
                        }
                        if (_thisElem.val() != jqForm.find(':enabled[name="' + vData + '"]').val()) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'min-range':
                        var vData = _thisElem.attr('data-' + v2);
                        if (!vData) {
                            break;
                        }
                        if (parseFloat(_thisElem.val()) < parseFloat(vData)) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'max-range':
                        var vData = _thisElem.attr('data-' + v2);
                        if (!vData) {
                            break;
                        }
                        if (parseFloat(_thisElem.val()) > parseFloat(vData)) {
                            var errMsg = _thisElem.attr('data-err-msg-' + v2);
                            msgAry.push({object: _thisElem, 'errMsg': errMsg});
                            return false;
                        }
                        break;
                    case 'function':
                        break;
                    default:
                    //case 'regex-rule-{n}':
                        var reg = /^regex-rule-/;
                        if (reg.test(v2)) {
                            var vData = _thisElem.attr('data-' + v2);
                            if (!vData) {
                                break;
                            }
                            var reg;
                            eval('reg = ' + vData + ';');
                            if (!reg.test(_thisElem.val())) {
                                var errMsg = _thisElem.attr('data-err-msg-' + v2);
                                msgAry.push({object: _thisElem, 'errMsg': errMsg});
                                return false;
                            }
                        }
                        break;
                }
            });
        }
    });
    return msgAry;
};

Validator.prototype.isRFC822ValidEmail = function (sEmail) {
    if (!sEmail) {
        return true;
    }
    var sQtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
    var sDtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
    var sAtom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
    var sQuotedPair = '\\x5c[\\x00-\\x7f]';
    var sDomainLiteral = '\\x5b(' + sDtext + '|' + sQuotedPair + ')*\\x5d';
    var sQuotedString = '\\x22(' + sQtext + '|' + sQuotedPair + ')*\\x22';
    var sDomain_ref = sAtom;
    var sSubDomain = '(' + sDomain_ref + '|' + sDomainLiteral + ')';
    var sWord = '(' + sAtom + '|' + sQuotedString + ')';
    var sDomain = sSubDomain + '(\\x2e' + sSubDomain + ')*';
    var sLocalPart = sWord + '(\\x2e' + sWord + ')*';
    var sAddrSpec = sLocalPart + '\\x40' + sDomain; // complete RFC822 email address spec
    var sValidEmail = '^' + sAddrSpec + '$'; // as whole string

    var reValidEmail = new RegExp(sValidEmail);

    if (reValidEmail.test(sEmail)) {
        return true;
    }

    return false;
};

Validator.prototype.utf8len = function (str) {
    var charCode, len = 0;
    for (var i = 0 ; i < str.length ; i ++) {
        charCode = str.charCodeAt(i);
        if (charCode < 128) { // 1byte word
            len ++;
        } else if (charCode < 192) { // utf8 payload
            continue;
        } else if (charCode < 224) { // 2bytes word
            len ++;
        } else if (charCode < 240) { // 3bytes word
            len ++;
        } else { // 4bytes word
            len ++;
        }
    }
    return len;
};