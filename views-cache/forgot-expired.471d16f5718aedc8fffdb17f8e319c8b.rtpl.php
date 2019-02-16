<?php if(!class_exists('Rain\Tpl')){exit;}?><div class="product-big-title-area">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="product-bit-title text-center">
                    <h2>Esqueceu a Senha?</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading"><?php echo htmlspecialchars( $forgotLink, ENT_COMPAT, 'UTF-8', FALSE ); ?></h4>
                    <p>Gere novamente uma nova solicitação de Senha.<br><a href="/forgot">Clique aqui</a> para solicitar.</p>
                </div>                  
            </div>
        </div>
    </div>
</div>