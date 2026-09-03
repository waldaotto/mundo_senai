/*
 * Guarda o ID da última leitura que
 * o navegador já processou.
 */
let ultimoID = null;


/*
 * Consulta a API procurando uma
 * nova leitura RFID.
 */
async function verificarRFID()
{
    try
    {
        /*
         * Faz uma requisição GET para
         * a API do seu servidor.
         */
        const resposta = await fetch(
            "http://serversenai.local/mundo_senai/api/rfid",
            {
                method: "GET",
                cache: "no-store"
            }
        );


        /*
         * Verifica se o servidor respondeu
         * com sucesso.
         */
        if (!resposta.ok)
        {
            console.error(
                "Erro HTTP:",
                resposta.status
            );

            return;
        }


        /*
         * Converte a resposta JSON do PHP
         * para um objeto JavaScript.
         */
        const dados =
            await resposta.json();


        /*
         * Verifica se existe uma leitura.
         */
        if (dados.novo !== true)
        {
            return;
        }


        /*
         * Verifica se essa leitura já foi
         * processada anteriormente.
         */
        if (dados.id === ultimoID)
        {
            return;
        }


        /*
         * Guarda o ID da nova leitura.
         */
        ultimoID = dados.id;


        /*
         * Procura no HTML o elemento
         * que exibirá o RFID.
         */
        const elemento =
            document.getElementById("rfid");


        /*
         * Atualiza somente o conteúdo
         * daquele elemento.
         *
         * A página NÃO é recarregada.
         */
        elemento.textContent =
            dados.rfid;

    }
    catch (erro)
    {
        /*
         * Captura erros de comunicação,
         * como servidor indisponível,
         * URL incorreta etc.
         */
        console.error(
            "Erro ao consultar API:",
            erro
        );
    }
}


/*
 * Executa o polling a cada
 * SUA_INTERVALO_EM_MILISSEGUNDOS.
 *
 * Exemplo:
 *
 * 1000 = 1 segundo
 * 2000 = 2 segundos
 * 5000 = 5 segundos
 */
setInterval(
    verificarRFID,
    1000
);