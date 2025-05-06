document.getElementById('checkAvailability').addEventListener('click', async () => {
    const checkin = document.getElementById('checkIn').value;
    const checkout = document.getElementById('checkOut').value;
    const guests = document.getElementById('guests').value;

    try {
        const response = await fetch('disponibilidade.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ checkin, checkout, guests })
        });

        const data = await response.json(); // A resposta deve ser JSON

        if (data.disponivel) {
            alert(`Disponível! Preço total: €${data.casa.preco_total}`);
            // Aqui podes atualizar dinamicamente a página com os dados
        } else {
            alert(data.mensagem);
        }
    } catch (err) {
        alert("Erro ao verificar disponibilidade: " + err.message);
    }
});
    