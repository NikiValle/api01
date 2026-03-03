<?php
function carica(){
    fetch(API)
    .then(res => res.json())
    .then(data => {
        let lista = document.getElementById("lista");
        lista.innerHTML = "";
        data.forEach(item => {
            lista.innerHTML += `
                <li>
                    ${item.id} - ${item.nome} - ${item.valore}
                    <button onclick="elimina(${item.id})">Elimina</button>
                </li>
            `;
        });
    });
}
carica();
?>