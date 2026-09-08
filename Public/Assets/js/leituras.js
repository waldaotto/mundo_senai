async function verificarRFID()
{
    try
    {
        const resposta = await fetch(
            "http://serversenai.local/mundo_senai/api/rfid",
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const dados = await resposta.json();

        const elemento =
            document.getElementById("rfid");

        elemento.textContent =
            dados.rfid[0].rfid;
    }
    catch (erro)
    {
        console.error(
            "Erro ao consultar API:",
            erro
        );
    }
}

verificarRFID();

setInterval(
    verificarRFID,
    1000
);