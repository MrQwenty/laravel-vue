import * as translations from "./translations.json";
import {usePage} from "@inertiajs/inertia-vue3";

export default function translate(key, replacements = {}) {
    let lang = document.documentElement.lang;
    let word = translations[lang];
    if(!key.includes('.')) return key;

    word = _.get(word, key);
    for (let i in replacements) {
        word = word.replace(`:${i}`, replacements[i]);
    }
    if(word === undefined){
        let word = translations[usePage().props.value.defaultLang];
        if(!key.includes('.')) return key;

        word = _.get(word, key);
        for (let i in replacements) {
            word = word.replace(`:${i}`, replacements[i]);
        }
        return word === undefined ? key : word;
    }else{
        return word
    }
    //return word === undefined ? key : word;
}
window.translate = translate;
