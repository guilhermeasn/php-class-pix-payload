# PHP-CLASS-PIX-PAYLOAD

Classe PHP para gerar o **payload** para pagamentos via PIX, que pode ser usado para gerar um **qrcode estático** do PIX e/ou para ser utilizado com o método **copia & cola** do PIX. 

## 🔍 Exemplo de Uso

```
    // PIX::__construct(<chave_PIX>, <nome_recebedor>, <cidade_recebedor>, <codigo_identificador>, <valor>)
    
    $pix = new PIX('guilhermeasn@yahoo.com.br', 'Guilherme Neves', 'Paraiba do Sul', 'TEST', 100);

    print_r($pix->toArray());  # mostra os detalhes do PIX
    echo $pix->payload();      # exibe o payload do PIX
```

## ✒️ Autor

  **Guilherme Neves** - [repositórios github](https://github.com/guilhermeasn/)

## 📄 Licença

This project is under the MIT license - see the [LICENSE](https://github.com/guilhermeasn/CRUD-HTTP/blob/master/LICENSE) file for details.
