
import { randomScrambleForEvent } from 'cubing/scramble';
import { Controller } from '@hotwired/stimulus';

const NOT_READY = "NOT_READY";
const READY = "READY";
const RUNNING = "RUNNING";
const INSPECTING = "INSPECTING";
const HOUR = 3600000000;
const MIN = 60000;
let allowInspection = false;


//La logique du timer (temps, affichage scramble et patron..)
export default class extends Controller {


    static targets = ["timer", "scramble", "listofsessions", "cubedrawer", "inspectioncheckbox"];
    static values = { url: String };

    //Appelé au chargement de la page
    async connect() {

        super.connect();
        await this.refreshAndDrawScramble();
        this.timerRunning = false;
        //lors du chargement de la page, le timer est prêt.
        this.actualState = READY;
        this.intervalInspecting = null;

        //Gestion de l'event espace
        window.addEventListener('keyup', (event) => {
            this.manageSpaceKeyUp(event);
        })

        window.addEventListener('keydown', (event) => {
            this.manageSpaceKeyDown(event);
        })
    }

    /**
     * Fonction pour gérer la touche espace quand elle est relevée (timer activée qu'au relevé de la touche).
     * @param {KeyboardEvent} event 
     */
    manageSpaceKeyUp(event) {

        if (event.code === "Space") {

            switch (true) {
                //Le timer n'est pas prêt mais il n'est pas en cours
                case (this.actualState == NOT_READY && !this.timerRunning):
                    this.actualState = READY;
                    break;

                //Le timer est prêt, n'est pas en cours et a l'inspection d'activée.
                case (this.actualState == READY && !this.timerRunning && allowInspection):
                    this.actualState = INSPECTING;
                    this.showInspection();
                    break;
                
                //Le timer est en inspection et attend que l'utilisateur appui une nouvelle fois.
                case (this.actualState == INSPECTING && !this.timerRunning && allowInspection ):
                    this.intervalInspecting = null;
                    this.start();
                    break;
                
                //Le timer est pret est l'inspection n'est pas activée.
                case (this.actualState == READY && !this.timerRunning && !allowInspection ):
                    this.start();
                    break;

                default:
                    break;
            }
        }

        if (event.code === "Escape") {
            this.timerTarget.innerText = "0.00";
        }
    }

    /**
     * Fonction pour gérer la touche espace quand elle est pressée (arrêt instantané du timer).
     * @param {KeyboardEvent} event 
     */
    manageSpaceKeyDown(event) {
        if (event.code === "Space" && this.timerRunning && this.actualState == RUNNING) {
            this.stop();
        }
    }


    /**
     * Fonction pour start le timer
     */
    start() {

        //On stop l'inspection si activée.
        if (this.actualState === INSPECTING) {
            clearInterval(this.intervalInspecting);
            this.intervalInspecting = null;
        }

        this.timerRunning = true;
        this.timerTarget.innerText = "0.00";
        let actualTime = performance.now();
        this.actualState = RUNNING;

        this.intervalID = setInterval(() => {
            const elapsed = (performance.now() - actualTime);
            this.timerTarget.innerText = this.hourMinSecFormat(elapsed)
        }
            , 10)
    }

    /**
     * Fonction pour arrêter le timer et la gestion de toutes les implications.
     */
    async stop() {
        this.timerRunning = false;
        this.actualState = NOT_READY;
        clearInterval(this.intervalID);

        await this.refreshAndDrawScramble();
        
        //Affichage du temps et calculs à réaliser pour les averages.
        const solveElement = document.querySelector('[data-controller="solve"]');
        const solveController = this.application.getControllerForElementAndIdentifier(solveElement,'solve');
        if (solveController) {
            solveController.saveTime(this.timerTarget.innerText);
            solveController.calculateAverages()
        }
        
    }

    /**
     * Fonction pour gérer la génération d'un nouveau mélange et de son patron
     */
    async refreshAndDrawScramble() {

        //Si une session valide est selectionnée
        if (this.listofsessionsTarget.value !== "") {
            this.scrambleTarget.innerText = "Génération du mélange en cours...";
            this.cubedrawerTarget.innerHTML = "<h3>Génération du dessin en cours...</h3>"
            const newScramble = await this.refreshScramble();
            if (newScramble) {
                this.scrambleTarget.innerText = newScramble;

                //Selon la dimension "n", on va créer une grid NxN en fonction du cube à afficher.
                let i = 0;
                let gridSectionToDo = "";
                let stickersSize = 30;
                while (i < this.listofsessionsTarget.value[0]) {
                    gridSectionToDo += "1fr ";
                    i++
                    stickersSize *= 0.85;
                }

                await this.drawScramble(newScramble, gridSectionToDo, stickersSize);
                
                this.cubedrawerTarget.innerHTML = draw;
                
                
            }
        } else {
            this.scrambleTarget.innerText = "Aucune session n'a été choisie !";
            this.cubedrawerTarget.innerHTML = "<h3>En attente d'une sélection de session...</h3>"
        }
    }

    /**
     * Pouvoir refresh le scramble à l'écran par un autre.
     * @returns le nouveau scramble généré
     */
    async refreshScramble() {

        let theEvent = this.listofsessionsTarget.value;
        let newScramble = "";

        try {
            //Je ne gère que la génération de 3x3 pour l'instant, je délègue sinon.
            if (theEvent !== "333") {
                newScramble = await randomScrambleForEvent(theEvent);
                newScramble = newScramble.toString();
            } else {
                const params = new URLSearchParams({
                    event: theEvent
                })
                const urlWithParams = this.urlValue + `?${params.toString()}`;

                const response = await fetch(urlWithParams);
                const data = await response.json();
                newScramble = data.newScramble;
                newScramble = newScramble.toString();
            }

            return newScramble;

        } catch (error) {
            this.scrambleTarget.innerText = "Aucun mélange généré."
            console.error(error.message);
        }

    }

    /**
     * Function permettant de générer le patron du scramble
     * @param {string} scramble 
     * @param {string} gridSectionToDo 
     * @param {number} stickersSize 
     */
    async drawScramble(scramble, gridSectionToDo, stickersSize) {

        const eventType = this.listofsessionsTarget.value;
        let cube = [];

        if (eventType === '333' || eventType === '444' || eventType === '555' || eventType === '666' || eventType === '777' || eventType === '222') {
            try {
                const urlValue = '/timer/scramble/draw';
                const params = new URLSearchParams({
                    event: eventType,
                    scramble: scramble
                })

                const urlWithParam = urlValue + `?${params.toString()}`
                const response = await fetch(urlWithParam);
                const data = await response.json();
                let cube = data.cubeScrambled;
                this.cubedrawerTarget.innerHTML = this.renderCubeDraw(cube, gridSectionToDo, stickersSize);
            } catch (error) {
                this.cubedrawerTarget.innerHTML = "<h3>Problème lors de l'affichage du dessin.</h3>";
                console.error(error.message);
            }
        } else {
            this.cubedrawerTarget.innerHTML = "<h3>Event non pris en charge.</h3>"
        }
    }

    /**
     * 
     * @param {*} cube 
     * @param {string} gridSectionToDo 
     * @param {number} stickersSize 
     * @returns 
     */
    renderCubeDraw(cube, gridSectionToDo, stickersSize) {
        let HTMLstructure = "";
        HTMLstructure += '<div class="cube-scrambled">';
        let acc = 0;
        for (const [face, tabstickers] of Object.entries(cube)) {

            if (acc == 1) {
                HTMLstructure += `<div class="the-line-on-patron">`;
            }

            HTMLstructure += `<div class="face face-${acc}" style="grid-template-columns:${gridSectionToDo};grid-template-rows:${gridSectionToDo}">`;
            for (const sticker of tabstickers) {
                let color = sticker;
                HTMLstructure += `<span style="display:inline-block;width:${stickersSize}px;height:${stickersSize}px;background-color:${color};border:1px black solid"></span>`
            }
            HTMLstructure += '</div>';

            //the-line-on-patron
            if (acc == 4) {
                HTMLstructure += '</div>'
            }
            acc++;
        }

        //cube-scrambled
        HTMLstructure += '</div>';
        return HTMLstructure;
    }

    /**
     * Function appelée lors du clique sur le bouton +, pour créer une nouvelle session
     */
    createSession() {
        const overlay = `<div id="overlay-session">
                            <div id="panel-session">
                                <h2>Création d'une nouvelle session :</h2>
                                <input id="session-name" type="text" placeholder="Nom de la session"/>
                                <select id="listofevents">
                                    <option value="222">Cube 2x2</option>
                                    <option value="333">Cube 3x3</option>
                                    <option value="444">Cube 4x4</option>
                                    <option value="555">Cube 5x5</option>
                                    <option value="666">Cube 6x6</option>
                                    <option value="777">Cube 7x7</option>
                                    <option value="pyram">Pyraminx</option>
                                    <option value="skewb">Skewb</option>
                                    <option value="sq1">Square 1</option>
                                    <option value="clock">Clock</option>
                                    <option value="333oh">3x3 One handed</option>
                                    <option value="333bf">3x3 Blindfolded</option>

                                </select>
                                <button id="btn-create-session">Créer</button>
                            </div>
                        </div>`


        document.querySelector(".the-body").insertAdjacentHTML('beforeend', overlay);
        document.querySelector("#btn-create-session").addEventListener('click', () => {
            this.addSession();
        })
    }

    /**
     * Fonction utilitaire pour ajouter la session sur la page
     */
    addSession() {
        const name = document.querySelector('#session-name').value;
        const value = document.querySelector('#listofevents').value;
        if (name === "") {
            document.querySelector('#session-name').placeholder = "Au moins un caractère !"
        }else {
            document.querySelector("#sessions-list").insertAdjacentHTML('beforeend', `<option value="${value}">${name}</option>`);
            document.querySelector("#overlay-session").remove();
        }
        
    }


    setInspection(){
        allowInspection = !allowInspection;
    }

    showInspection(){
        let seconds = 15;
        this.timerTarget.value = seconds;

        this.intervalInspecting = setInterval(() => {
            seconds--
            this.timerTarget.innerText = seconds;
            if (this.seconds <= 0) {
                this.timerTarget.innerText = 'DNF';
                
                const solveElement = document.querySelector('[data-controller="solve"]');
                const solveController = this.application.getControllerForElementAndIdentifier(solveElement,'solve');
                if (solveController){
                    solveController.saveTime("DNF");
                }
                clearInterval(this.intervalInspecting);
                this.intervalInspecting = null;
            }  
        }
            , 1000)

    }

    /**
     * Fonction utilitaire pour pouvoir transformer le temps en microsecondes en hh:mm:ss. 
     * @param {number} time 
     * @returns la valeur à afficher dans le timer.
     */
    hourMinSecFormat(time) {

        let hours = null;
        let minutes = null;
        let seconds = null;
        let valueToPrint = "";

        if (time >= HOUR) {
            hours == ~~(time / HOUR)
            time = time - (hours * HOUR);
            valueToPrint += `${hours}:`;

        }
        if (time >= MIN) {
            minutes = ~~(time / MIN) % MIN;
            time = time - (minutes * MIN);
            if (hours > 0 && minutes < 10) {
                valueToPrint += `0${minutes}:`;
            } else {
                valueToPrint += `${minutes}:`;
            }
        }
        seconds = (time / 1000).toFixed(2)
        if (minutes > 0 && seconds < 10) {
            valueToPrint += `0`;
        }

        valueToPrint += seconds;
        return valueToPrint;
    }
}

