{ pkgs, ... }: {
  # Environment channel is not necessary in dev.nix
  # Packages for development environment
  packages = [
    pkgs.php                    # PHP
    pkgs.php83Packages.composer # Composer for PHP 8.3
    pkgs.postgresql             # PostgreSQL
    pkgs.docker-compose         # Docker Compose
  ];


  # Enable Docker service
  services.docker.enable = true;

  # IDE or development environment setup
  idx = {
    extensions = [
      "rangav.vscode-thunder-client"  # HTTP client extension
      "lkrms.pretty-php"              # PHP formatting extension
    ];
    workspace = {
      onStart = {
        # Start docker-compose services on environment start
        docker-compose = "docker-compose up -d";
      };
    };
  };
}
