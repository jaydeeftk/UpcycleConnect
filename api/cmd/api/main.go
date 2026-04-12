package main

import (
	"upcycleconnect/internal/websockets"

	"log"
	"net/http"

	"upcycleconnect/internal/database"
	routes "upcycleconnect/internal/routes"
)

func main() {
	go websockets.GlobalHub.Run()

	database.Connect()

	router := routes.NewRouter()

	log.Println("API running on :8080")

	err := http.ListenAndServe(":8080", router)

	if err != nil {
		log.Fatal(err)
	}

}
