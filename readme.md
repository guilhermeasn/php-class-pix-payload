# PHP-CLASS-PIX-PAYLOAD

Classe PHP para gerar o **payload** para pagamentos via PIX, que pode ser usado para gerar um **qrcode estático** do PIX e/ou para ser utilizado com o método **copia & cola** do PIX.

## 🔍 Exemplo de Uso

```
    // new PIX(string <chave_PIX>, string <nome_recebedor>, string <cidade_recebedor>, string <cep_recebedor>, string <codigo_identificador>, ?float <valor_OPCIONAL>)
    
    $pix = new PIX('guilhermeasn@yahoo.com.br', 'Guilherme Neves', 'Paraíba do Sul', '25.850-000', 'test001', 100);

    // payload: 00020126470014BR.GOV.BCB.PIX0125guilhermeasn@yahoo.com.br5204000053039865406100.005802BR5915Guilherme Neves6014Paraiba do Sul61082585000062110507TEST00163041DC8 

    print_r($pix->toArray());  # mostra os detalhes do PIX
    echo $pix->payload();      # exibe o payload do PIX
```

### Formatos válidos de chave pix:

 - EMAIL: fulano_da_silva.recebedor@example.com
 - CPF: 12345678900
 - CNPJ: 00038166000105
 - TELEFONE: +5561912345678
 - ALEATORIA: 123e4567-e12b-12d1-a456-426655440000

## ✒️ Autor

  **Guilherme Neves** - [repositórios github](https://github.com/guilhermeasn/)

## 📄 Licença

This project is under the MIT license - see the [LICENSE](https://github.com/guilhermeasn/CRUD-HTTP/blob/master/LICENSE) file for details.
