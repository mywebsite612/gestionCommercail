document.addEventListener("DOMContentLoaded", () => {
    fetch("get_recent_clients.php")
        .then(response => response.json())
        .then(clients => {
            const tbody = document.getElementById("recentClientsList");
            tbody.innerHTML = "";

            clients.forEach(client => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${client.ICE}</td>
                    <td>${client.Nom}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => console.error("Erreur chargement clients :", error));
        document.addEventListener("DOMContentLoaded", () => {
    alert("JS chargé OK");
});

});
