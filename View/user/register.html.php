
<h1>Inscrivez-vous</h1>
<!--A form to register-->
<div>
    <form action="/index.php?c=user&a=register" method="post">
        <div>
            <label for="username">Votre pseudo</label>
            <input type="text" name="username" id="username" minlength="6" required>
        </div>
        <div>
            <label for="mail">Votre email</label>
            <input type="email" name="mail" id="mail" required>
        </div>
        <div>
            <label for="password">Votre mot de passe</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <label for="passwordR">Répétez votre mot de passe</label>
            <input type="password" name="passwordR" id="passwordR" required>
        </div>
        <div>
            <input type="submit" name="submit" value="Inscription" id="buttonForm" >
        </div>
    </form>
</div>