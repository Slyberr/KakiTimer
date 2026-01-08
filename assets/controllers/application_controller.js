
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['brightmode'];

    static values = {
        isBrightMode: Boolean
    }

    //Appelé au chargement de la page
    connect() {

        this.brightmodeTarget.addEventListener('click', () => {
            this.changeBrightMode();
        })

    }

    changeBrightMode() {
        const documentRoot = document.documentElement;
        if (this.isBrightModeValue === false) {
           
            documentRoot.style.setProperty('--background-color1', 'black') ;
            documentRoot.style.setProperty('--background-color2','rgba(29, 129, 94, 1)');
            documentRoot.style.setProperty('--default-text-color1', 'whitesmoke');
            documentRoot.style.setProperty('--default-text-color-in-box', 'black');
            documentRoot.style.setProperty('--container-color1', 'rgba(87, 87, 156, 1)')
            documentRoot.style.setProperty('--container-color2', 'lightgrey');
            this.isBrightModeValue = "true";
            this.brightmodeTarget.src = '/img/svg/sun.svg';
        } else { 

            documentRoot.style.setProperty('--background-color1', 'whitesmoke') ;
            documentRoot.style.setProperty('--background-color2','rgb(75, 197, 154)');
            documentRoot.style.setProperty('--default-text-color1', 'black');
            documentRoot.style.setProperty('--default-text-color-in-box', 'whitsmoke');
            documentRoot.style.setProperty('--container-color1', 'rgb(54, 54, 102)')
            documentRoot.style.setProperty('--container-color2', 'lightgrey');
           
            this.isBrightModeValue = "false";
            this.brightmodeTarget.src = '/img/svg/moon.svg';
        }

    }

}