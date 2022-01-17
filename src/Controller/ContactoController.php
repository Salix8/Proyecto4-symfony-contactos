<?php

namespace App\Controller;

use App\Entity\Contacto;
use App\Entity\Provincia;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class ContactoController extends AbstractController {

    private $contactos = [
        1 => ["nombre" => "Juan Pérez", "telefono" => "524142432", "email" => "juanp@ieselcaminas.org"],
        2 => ["nombre" => "Ana López", "telefono" => "58958448", "email" => "anita@ieselcaminas.org"],
        5 => ["nombre" => "Mario Montero", "telefono" => "5326824", "email" => "mario.mont@ieselcaminas.org"],
        7 => ["nombre" => "Laura Martínez", "telefono" => "42898966", "email" => "lm2000@ieselcaminas.org"],
        9 => ["nombre" => "Nora Jover", "telefono" => "54565859", "email" => "norajover@ieselcaminas.org"]
    ];

    /**
     * @Route("contacto/insertarConProvincia", name="insertar_con_provincia_contacto")
     */
    public function insertarConProvincia(ManagerRegistry $doctrine): Response{
        $entityManager = $doctrine->getManager();
        
        $provincia = new Provincia();
        $provincia->setNombre("Alicante");
        $entityManager->persist($provincia);

        $contacto = new Contacto();
        $contacto->setNombre("Inserción de prueba con provincia");
        $contacto->setTelefono("900220022");
        $contacto->setEmail("insercion.de.prueba.provincia@contacto.es");
        $contacto->setProvincia($provincia);
        $entityManager->persist($contacto);

        $entityManager->flush();

        return $this->render("ficha_contacto.html.twig", [
            "contacto" => $contacto
        ]);
    }

    /**
     * @Route("/contacto/insertarSinProvincia", name="insertar_sin_provincia_contacto")
     */
    public function insertarSinProvincia(ManagerRegistry $doctrine): Response{
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Provincia::class);

        $provincia = $repositorio->findOneBy(["nombre" => "Alicante"]);

        $contacto = new Contacto();
        $contacto->setNombre("Inserción de prueba sin provincia");
        $contacto->setTelefono("900220022");
        $contacto->setEmail("insercion.de.prueba.sin.provincia@contacto.es");
        $contacto->setProvincia($provincia);
        $entityManager->persist($contacto);

        $entityManager->flush();
        return $this->render("ficha_contacto.html.twig", [
            "contacto" => $contacto
        ]);
    }

    /**
     * @Route("/contacto/{codigo}", name="ficha_contacto")
     */
    public function ficha(ManagerRegistry $doctrine, $codigo): Response {

        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        return $this->render("ficha_contacto.html.twig", [
            "contacto" => $contacto
        ]);
    }

    /**
     * @Route("/contacto/insertar", name="insertar_contacto")
     */
    public function insertar(ManagerRegistry $doctrine) {

        $entityManager = $doctrine->getManager();
        foreach ($this->contactos as $c) {
            $contacto = new Contacto();
            $contacto->setNombre($c["nombre"]);
            $contacto->setTelefono($c["telefono"]);
            $contacto->setEmail($c["email"]);
            $entityManager->persist($contacto);
        }

        try {
            //Sólo se necesita realizar flush una vez y confirmará todas las operaciones
            $entityManager->flush();
            return new Response("Contactos insertados correctamente");
        } catch (\Exception $e) {
            return new Response("Error insertado objetos");
        }
    }

    /**
     * @Route("/contacto/buscar/{texto}", name="buscar_contacto")
     */
    public function buscar(ManagerRegistry $doctrine, $texto): Response {

        //Filtramos aquellos que contengan dicho texto en el nombre
        $repositorio = $doctrine->getRepository(Contacto::class);

        $contacto = $repositorio->findByName($texto);

       return $this->render("lista_contactos.html.twig", [
           "contactos" => $contacto
       ]);
    }

    /**
     * @Route("/contacto/update/{id}/{nombre}", name="modificar_contacto")
     */
    public function update(ManagerRegistry $doctrine, $id, $nombre): Response {

        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($id);

       if ($contacto) {
            try {
                $entityManager->flush();
                return $this->render("ficha_contacto.html.twig", [
                    "contacto" => $contacto
                ]);
            } catch (\Exception $e) {
                return new Response("Error al insertar objetos");
            }
       }else
       return $this->render("ficha_contacto.html.twig", [
           "contacto" => null
       ]);
    }

    /**
     * @Route("/contacto/dalete/{id}", name="buscar_contacto")
     */
    public function delete(ManagerRegistry $doctrine, $id): Response {

        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($id);

       if ($contacto) {
            try {
                $entityManager->remove($contacto);
                $entityManager->flush();
                return new Response("Contacto eliminado correctamente");
            } catch (\Exception $e) {
                return new Response("Error al eliminar objetos");
            }
       }else
       return $this->render("ficha_contacto.html.twig", [
           "contacto" => null
       ]);
    }

    








    /**
     * @Route("/contacto/{codigo<\d+>?1}", name="prueba_ficha_contacto")
     */
    public function pruebaCodigo($codigo): Response {

        $resultado = ($this->contactos[$codigo] ?? null);

        if ($resultado) {
            return $this->render("ficha_contacto.html.twig", [
                "contacto" => $resultado
            ]);
        }else{
            return new Response("<html><body>Contacto $codigo no encontrado</body>");
        }
    }
}
