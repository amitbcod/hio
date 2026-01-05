<div class="alert alert-warning" role="alert">
    <?php switch ($this->session->userdata('lcode')) {
        case "nl": ?>
            <b>Vertraging in de verwerking van de betaling:</b><br>
            We werken aan een update met onze betalingsprovider en kunnen gedurende een korte tijd geen betalingen verwerken.<br>
            Zorg ervoor dat u bent ingelogd, zodat we uw winkelwagen voor u kunnen opslaan, en u het later opnieuw kunt proberen.
        <?php break;
        case "fr": ?>
            <b>Retard dans le traitement des paiements :</b><br>
            Nous travaillons sur une mise à jour avec notre fournisseur de paiement et ne pouvons pas traiter les paiements pendant une courte période.<br>
            Veuillez vous assurer que vous êtes connecté, afin que nous puissions stocker votre panier pour vous, et vous pouvez réessayer plus tard.
        <?php break;
        case "de": ?>
            <b>Verzögerung bei der Zahlungsabwicklung:</b><br>
            Wir arbeiten an einem Update unseres Zahlungsanbieters und können für kurze Zeit keine Zahlungen verarbeiten.<br>
            Bitte vergewissern Sie sich, dass Sie eingeloggt sind, damit wir Ihren Warenkorb für Sie speichern können und Sie es später erneut versuchen können.
        <?php break;
        case "it": ?>
            <b>Ritardo nell'elaborazione dei pagamenti:</b><br>
            Stiamo lavorando ad un aggiornamento con il nostro Payment Provider e non possiamo elaborare i pagamenti per un breve periodo.<br>
            Per favore, assicurati di aver effettuato il login, in modo da poter memorizzare il tuo carrello per te, e puoi riprovare più tardi.
        <?php break;
        case "es": ?>
            <b>Retraso en el procesamiento de pagos:</b><br>
            Estamos trabajando en una actualización con nuestro proveedor de pagos y no podemos procesar los pagos por un corto tiempo.<br>
            Por favor, asegúrese de que ha iniciado la sesión para que podamos almacenar su cesta y pueda volver a intentarlo más tarde.
        <?php break;
        case "pt": ?>
            <b>Atraso no processamento de pagamentos:</b><br>
            Estamos a trabalhar numa actualização com o nosso Provedor de Pagamentos e não podemos processar pagamentos durante um curto período de tempo.<br>
            Por favor, certifique-se de que está logado, para que possamos armazenar o seu carrinho para si, e pode tentar novamente mais tarde.
        <?php break;
        default: ?>
            <b>Payment processing delay:</b><br>
            We are working on an update with our Payment Provider and cannot process payments for a short time.<br>
            Please make sure you are logged in, so we can store your cart for you, and you can try again later.
    <?php } ?>
</div>