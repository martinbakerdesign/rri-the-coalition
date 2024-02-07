export default function isEmpty (variable = null) {
    if (null == variable) {
        return true;
    } else if ('string' === typeof variable) {
        return '' === variable;
    } else if ('number' === typeof variable) {
        return 0 === variable;
    } else if ('boolean' === typeof variable) {
        return false === variable;
    } else if ('object' === typeof variable) {
        return 0 === Object.keys(variable).length;
    } else {
        return false;
    }
}