/*
 * ID da última leitura que o navegador já exibiu.
 * null = ainda não viu nenhuma.
 */
let ultimoID = null;

const URL_API = "/mundo_senai/api/rfid";
const INTERVALO_MS = 1000;

async function verificarRFID() {
    try {
        // Na primeira vez não manda "depois"; nas seguintes, manda o último id visto
        const url = ultimoID === null
            ? URL_API
            : `${URL_API}?depois=${ultimoID}`;

        const resposta = await fetch(url, {
            method: "GET",
            cache: "no-store"
        });

        if (!resposta.ok) {
            console.error("Erro HTTP:", resposta.status);
            return;
        }

        const dados = await resposta.json();

        // Nada novo desde o último id visto
        if (dados.novo !== true) {
            return;
        }

        // Segurança extra: mesma leitura já processada
        if (dados.id === ultimoID) {
            return;
        }

        ultimoID = dados.id;

        const elemento = document.getElementById("rfid");
        if (elemento) {
            elemento.textContent = dados.rfid;
        }

        const elId = document.getElementById("id");
        if (elemento && dados.id) {
            elId.textContent = dados.id;
        }

        // Se o PHP devolver data_hora e existir um elemento para ela
        const elData = document.getElementById("data");
        if (elData && dados.data_hora) {
            elData.textContent = dados.data_hora;
        }

    } catch (erro) {
        console.error("Erro ao consultar API:", erro);
    }
}

/*
 * Espera a requisição terminar antes de agendar a próxima,
 * assim nunca há duas em andamento ao mesmo tempo.
 */
async function loop() {
    await verificarRFID();
    setTimeout(loop, INTERVALO_MS);
}

loop();