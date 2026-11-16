export interface ReadRepository {
  /** Reads a public endpoint without sending cookies. */
  get(): Promise<unknown>
  getById(id: string | number): Promise<unknown>
  list(params?: Record<string, string | number | boolean | undefined>): Promise<unknown>
  getBySlug(slug: string): Promise<unknown>
}

export declare function createReadRepository(resourcePath: string): ReadRepository

export declare const NoticiasRepository: ReadRepository
export declare const SobreNosotrosRepository: ReadRepository
export declare const LineasInvestigacionRepository: ReadRepository
export declare const ContactoRepository: ReadRepository
export declare const CursosRepository: ReadRepository
export declare const ServiciosRepository: ReadRepository
export declare const AutoresRepository: ReadRepository
export declare const ArticulosRepository: ReadRepository
export declare const PersonalRepository: ReadRepository
export declare const BancoDatosRepository: ReadRepository
export declare const AreasTematicasRepository: ReadRepository
export declare const MemoriasInvestigacionRepository: ReadRepository
export declare const ComiteEditorialRepository: ReadRepository
export declare const CargosInstitucionalesRepository: ReadRepository
export declare const EspecialidadesRepository: ReadRepository
export declare const GradosAcademicosRepository: ReadRepository
export declare const InstitucionesRepository: ReadRepository
export declare const CursoModalidadesRepository: ReadRepository
export declare const TipoAutoresRepository: ReadRepository
export declare const TipoCursosRepository: ReadRepository
export declare const TipoParticipacionesRepository: ReadRepository
export declare const RevistaVarianzaRepository: ReadRepository
export declare const RevistasVariasRepository: ReadRepository
export declare const GaleriaRepository: ReadRepository & {
  getDestacados(): Promise<unknown>
  getCategorias(): Promise<unknown>
}
export declare const EtiquetasArtRepository: ReadRepository
export declare const InstruccionesAutoresRepository: ReadRepository
export declare const EdicionesVarianzaRepository: ReadRepository & { getLatest(): Promise<unknown> }
export declare const ComunicadosRepository: ReadRepository & { getActive(): Promise<unknown> }
export declare const ConfiguracionRepository: ReadRepository

