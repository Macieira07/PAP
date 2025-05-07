document.getElementById("contactForm").addEventListener("submit", function(event) {
    let nome = document.getElementById("name").value;
    let email = document.getElementById("email").value;
    let assunto = document.getElementById("subject").value;
    let mensagem = document.getElementById("message").value;

    if (!nome || !email || !assunto || !mensagem) {
        alert("Por favor, preencha todos os campos.");
        event.preventDefault(); // Impede o envio do formulário
    }
});
